export function formatDate(value?: string | null): string {
    if (!value) return '—';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

export function formatDateTime(value?: string | null): string {
    if (!value) return '—';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

export function formatTimestamp(milliseconds: number): string {
    const seconds = Math.floor(milliseconds / 1000);
    const minutes = Math.floor(seconds / 60);

    return `${String(minutes).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
}

export function titleCase(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export function matchTeamName(match: MatchRecord): string {
    const teamName = match.athletes?.map((athlete) => athlete.name).join(', ') || match.athlete?.name || 'Tim belum ditentukan';

    if (match.match_type?.startsWith('embu')) return teamName;

    return `${teamName} vs ${match.opponent_name || 'Lawan belum ditentukan'}`;
}
import type { MatchRecord } from '../types';
