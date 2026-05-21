<?php

namespace App\Support;

use App\Models\User;
use App\Models\Workspace;

final class WorkspaceNavigation
{
    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     teams_count: int,
     *     projects_count: int,
     *     teams: array<int, array{
     *         id: int,
     *         name: string,
     *         users_count: int,
     *         users: array<int, array{id: int, name: string, email: string}>
     *     }>,
     *     projects: array<int, array{
     *         id: int,
     *         name: string,
     *         status: string,
     *         tickets_count: int
     *     }>
     * }>
     */
    public static function forUser(User $user): array
    {
        return Workspace::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereHas('teams.users', fn ($query) => $query->whereKey($user->id))
                    ->orWhereHas('teams.legacyUsers', fn ($query) => $query->whereKey($user->id));
            })
            ->with([
                'teams' => fn ($query) => $query
                    ->where(function ($query) use ($user) {
                        $query
                            ->whereHas('workspace', fn ($query) => $query->where('owner_id', $user->id))
                            ->orWhereHas('users', fn ($query) => $query->whereKey($user->id))
                            ->orWhereHas('legacyUsers', fn ($query) => $query->whereKey($user->id));
                    })
                    ->with('users:id,name,email')
                    ->with('legacyUsers:id,team_id,name,email')
                    ->orderBy('name'),
                'projects' => fn ($query) => $query
                    ->where(function ($query) use ($user) {
                        $query
                            ->whereHas('workspace', fn ($query) => $query->where('owner_id', $user->id))
                            ->orWhereHas('teams.users', fn ($query) => $query->whereKey($user->id))
                            ->orWhereHas('teams.legacyUsers', fn ($query) => $query->whereKey($user->id));
                    })
                    ->withCount('tickets')
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Workspace $workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'teams_count' => $workspace->teams->count(),
                'projects_count' => $workspace->projects->count(),
                'teams' => $workspace->teams
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($team) => [
                        'id' => $team->id,
                        'name' => $team->name,
                        'users_count' => $team->users->concat($team->legacyUsers)->unique('id')->count(),
                        'users' => $team->users
                            ->concat($team->legacyUsers)
                            ->unique('id')
                            ->sortBy('name')
                            ->values()
                            ->map(fn ($member) => [
                                'id' => $member->id,
                                'name' => $member->name,
                                'email' => $member->email,
                            ])
                            ->all(),
                    ]),
                'projects' => $workspace->projects->map(fn ($project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'tickets_count' => $project->tickets_count,
                ])->all(),
            ])
            ->all();
    }
}
