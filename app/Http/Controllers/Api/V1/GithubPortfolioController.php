<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GithubPortfolioController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/portfolios/github/{username}
     * Laravel bertindak sebagai SERVICE CONSUMER yang memanggil GitHub REST API (Lab 2)
     */
    public function show(string $username): JsonResponse
    {
        try {
            $client = Http::accept('application/vnd.github+json')
                ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
                ->timeout(10);

            if ($token = env('GITHUB_TOKEN')) {
                $client = $client->withToken($token);
            }

            $userRes = $client->get("https://api.github.com/users/{$username}");

            if ($userRes->status() === 404) {
                return $this->problemResponse(
                    title: 'GitHub User Not Found',
                    detail: "Akun GitHub '{$username}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'github-user-not-found'
                );
            }

            if ($userRes->failed()) {
                return $this->problemResponse(
                    title: 'Bad Gateway — GitHub API Error',
                    detail: 'Gagal berkomunikasi dengan layanan eksternal GitHub REST API.',
                    status: 502,
                    errorSlug: 'github-upstream-error'
                );
            }

            $reposRes = $client->get("https://api.github.com/users/{$username}/repos", [
                'sort'     => 'updated',
                'per_page' => 3,
            ]);

            $profil = $userRes->json();
            $repos  = collect($reposRes->json())->map(fn ($r) => [
                'repo_name' => $r['name'] ?? null,
                'language'  => $r['language'] ?? null,
                'stars'     => $r['stargazers_count'] ?? 0,
                'html_url'  => $r['html_url'] ?? null,
            ]);

            return $this->successResponse(
                data: [
                    'github_login' => $profil['login'] ?? $username,
                    'display_name' => $profil['name'] ?? ($profil['login'] ?? $username),
                    'bio'          => $profil['bio'] ?? null,
                    'public_repos' => $profil['public_repos'] ?? 0,
                    'followers'    => $profil['followers'] ?? 0,
                    'profile_url'  => $profil['html_url'] ?? "https://github.com/{$username}",
                    'latest_repos' => $repos,
                ],
                message: 'Data portofolio koding mahasiswa berhasil diambil dari GitHub REST API.'
            );
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'External Service Timeout',
                detail: 'Koneksi ke layanan eksternal GitHub REST API mengalami timeout.',
                status: 504,
                errorSlug: 'github-timeout'
            );
        }
    }
}
