import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Swords } from 'lucide-react';
import { useState } from 'react';
import EmptyState from '../../Components/EmptyState';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { formatDate, matchTeamName, titleCase } from '../../lib/format';
import type { MatchRecord, Paginated } from '../../types';

export default function MatchIndex({ matches, filters, statusOptions, can }: { matches: Paginated<MatchRecord>; filters: { search: string; status: string }; statusOptions: string[]; can: { create: boolean } }) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get('/matches', { search, status }, { preserveState: true, replace: true });
    };

    return (
        <AppLayout title="Pertandingan" description="Pusat data pertandingan, sumber video, status analisis, dan hasil evaluasi." actions={can.create ? <Link href="/matches/create" className="button-primary"><Plus aria-hidden="true" className="size-4" /> Buat pertandingan</Link> : undefined}>
            <Head title="Pertandingan" />
            <section className="panel overflow-hidden rounded-xl">
                <form onSubmit={submit} className="grid gap-3 border-b border-line bg-slate-50/70 p-4 sm:grid-cols-[minmax(220px,1fr)_220px_auto]">
                    <div className="relative"><label htmlFor="match-search" className="sr-only">Cari pertandingan</label><Search aria-hidden="true" className="pointer-events-none absolute left-3 top-3.5 size-4 text-slate-500" /><input id="match-search" className="field-control pl-10" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari atlet atau lawan" /></div>
                    <div><label htmlFor="match-status" className="sr-only">Status pertandingan</label><select id="match-status" className="field-control" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">Semua status</option>{statusOptions.map((option) => <option key={option} value={option}>{titleCase(option)}</option>)}</select></div>
                    <button type="submit" className="button-secondary"><Search aria-hidden="true" className="size-4" /> Terapkan</button>
                </form>

                {matches.data.length ? <>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[840px] text-left text-sm">
                            <thead className="border-b border-line bg-white text-xs text-ink-soft"><tr><th className="px-5 py-3 font-semibold">Pertandingan</th><th className="px-5 py-3 font-semibold">Event</th><th className="px-5 py-3 font-semibold">Tanggal</th><th className="px-5 py-3 font-semibold">Media / analisis</th><th className="px-5 py-3 font-semibold">Hasil</th><th className="px-5 py-3 font-semibold">Status</th></tr></thead>
                            <tbody className="divide-y divide-line">
                                {matches.data.map((match) => <tr key={match.id} className="hover:bg-slate-50/70">
                                    <td className="px-5 py-4"><Link href={`/matches/${match.id}`} className="font-semibold text-navy hover:underline">{matchTeamName(match)}</Link><div className="mt-1 text-xs text-ink-soft">{match.category}</div></td>
                                    <td className="px-5 py-4 text-ink-soft">{match.competition_event?.name ?? 'Pertandingan mandiri'}</td>
                                    <td className="px-5 py-4 text-ink-soft">{formatDate(match.match_date)}</td>
                                    <td className="px-5 py-4"><span className="font-semibold">{match.videos_count ?? 0}</span> video · <span className="font-semibold">{match.analyses_count ?? 0}</span> analisis</td>
                                    <td className="px-5 py-4"><StatusBadge status={match.result} label={titleCase(match.result)} /></td>
                                    <td className="px-5 py-4"><StatusBadge status={match.status} label={titleCase(match.status)} /></td>
                                </tr>)}
                            </tbody>
                        </table>
                    </div>
                    <Pagination paginator={matches} />
                </> : <EmptyState icon={Swords} title="Belum ada pertandingan" description="Buat pertandingan untuk menghubungkan atlet, lawan, event, dan video analisis." action={can.create ? <Link href="/matches/create" className="button-primary">Buat pertandingan</Link> : undefined} />}
            </section>
        </AppLayout>
    );
}
