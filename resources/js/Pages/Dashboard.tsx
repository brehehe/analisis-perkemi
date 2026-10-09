import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    ArrowUpRight,
    CircleAlert,
    ClipboardCheck,
    PlayCircle,
    Swords,
    UsersRound,
    Video,
} from 'lucide-react';
import EmptyState from '../Components/EmptyState';
import StatusBadge from '../Components/StatusBadge';
import AppLayout from '../Layouts/AppLayout';
import { formatDate, matchTeamName, titleCase } from '../lib/format';
import type { MatchRecord } from '../types';

type Props = {
    stats: {
        athletes: number;
        matches: number;
        videos: number;
        completed_analyses: number;
        average_score: number;
    };
    processing: { queued: number; active: number; failed: number };
    recentMatches: MatchRecord[];
    performanceTrend: Array<{
        id: string;
        overall_score: string;
        completed_at: string;
        match_record: { id: string; match_date: string };
    }>;
};

const metricCards = [
    { key: 'athletes', label: 'Atlet aktif', icon: UsersRound, tone: 'text-blue-800 bg-blue-50' },
    { key: 'matches', label: 'Pertandingan', icon: Swords, tone: 'text-slate-800 bg-slate-100' },
    { key: 'videos', label: 'Video tersimpan', icon: Video, tone: 'text-amber-900 bg-amber-50' },
    { key: 'completed_analyses', label: 'Analisis selesai', icon: ClipboardCheck, tone: 'text-emerald-800 bg-emerald-50' },
] as const;

export default function Dashboard({ stats, processing, recentMatches, performanceTrend }: Props) {
    return (
        <AppLayout
            title="Dashboard performa"
            description="Ringkasan operasional pertandingan, progres analisis, dan indikator yang membutuhkan perhatian pelatih."
            actions={<Link href="/matches/create" className="button-primary"><Swords aria-hidden="true" className="size-4" /> Buat pertandingan</Link>}
        >
            <Head title="Dashboard" />

            <section aria-labelledby="overview-heading">
                <div className="mb-3 flex items-center justify-between">
                    <h2 id="overview-heading" className="text-sm font-semibold text-ink">Ringkasan saat ini</h2>
                    <span className="text-xs text-ink-soft">Data operasional langsung</span>
                </div>
                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {metricCards.map((metric) => {
                        const Icon = metric.icon;
                        return (
                            <div key={metric.key} className="panel rounded-xl p-5">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="text-sm text-ink-soft">{metric.label}</p>
                                        <p className="mt-3 text-3xl font-semibold tracking-[-0.035em] text-ink">{stats[metric.key]}</p>
                                    </div>
                                    <div className={`grid size-10 place-items-center rounded-lg ${metric.tone}`}>
                                        <Icon aria-hidden="true" className="size-5" />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </section>

            <div className="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.75fr)]">
                <section className="panel overflow-hidden rounded-xl" aria-labelledby="recent-heading">
                    <div className="flex items-center justify-between border-b border-line px-5 py-4">
                        <div>
                            <h2 id="recent-heading" className="font-semibold">Pertandingan terbaru</h2>
                            <p className="mt-1 text-xs text-ink-soft">Akses cepat ke rekaman dan hasil analisis.</p>
                        </div>
                        <Link href="/matches" className="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-navy hover:underline">
                            Lihat semua <ArrowUpRight aria-hidden="true" className="size-4" />
                        </Link>
                    </div>

                    {recentMatches.length ? (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[680px] text-left text-sm">
                                <thead className="bg-slate-50 text-xs text-ink-soft">
                                    <tr>
                                        <th className="px-5 py-3 font-semibold">Peserta</th>
                                        <th className="px-5 py-3 font-semibold">Lawan / format</th>
                                        <th className="px-5 py-3 font-semibold">Event</th>
                                        <th className="px-5 py-3 font-semibold">Tanggal</th>
                                        <th className="px-5 py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-line">
                                    {recentMatches.map((match) => (
                                        <tr key={match.id} className="transition-colors hover:bg-slate-50/80">
                                            <td className="px-5 py-4">
                                                <Link href={`/matches/${match.id}`} className="font-semibold text-navy hover:underline">{matchTeamName(match)}</Link>
                                                <div className="mt-1 text-xs text-ink-soft">{match.category}</div>
                                            </td>
                                            <td className="px-5 py-4 font-medium">{match.match_type?.startsWith('embu') ? 'Satu tim' : match.opponent_name}</td>
                                            <td className="px-5 py-4 text-ink-soft">{match.competition_event?.name ?? 'Pertandingan mandiri'}</td>
                                            <td className="px-5 py-4 text-ink-soft">{formatDate(match.match_date)}</td>
                                            <td className="px-5 py-4"><StatusBadge status={match.status} label={titleCase(match.status)} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <EmptyState icon={Swords} title="Belum ada pertandingan" description="Buat pertandingan pertama untuk mulai menyimpan video dan analisis performa." action={<Link href="/matches/create" className="button-primary">Buat pertandingan</Link>} />
                    )}
                </section>

                <section className="panel rounded-xl p-5" aria-labelledby="queue-heading">
                    <div className="flex items-start justify-between">
                        <div>
                            <h2 id="queue-heading" className="font-semibold">Status pemrosesan</h2>
                            <p className="mt-1 text-xs text-ink-soft">Kesehatan antrean analisis video.</p>
                        </div>
                        <Activity aria-hidden="true" className="size-5 text-navy" />
                    </div>
                    <div className="mt-6 grid gap-3">
                        <Link href="/analyses?status=queued" className="flex min-h-16 items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4">
                            <span className="flex items-center gap-3 text-sm font-medium text-amber-950"><PlayCircle aria-hidden="true" className="size-5" /> Menunggu antrean</span>
                            <strong className="text-2xl text-amber-950">{processing.queued}</strong>
                        </Link>
                        <Link href="/analyses?status=active" className="flex min-h-16 items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-4">
                            <span className="flex items-center gap-3 text-sm font-medium text-blue-950"><Activity aria-hidden="true" className="size-5" /> Sedang diproses</span>
                            <strong className="text-2xl text-blue-950">{processing.active}</strong>
                        </Link>
                        <Link href="/analyses?status=failed" className="flex min-h-16 items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4">
                            <span className="flex items-center gap-3 text-sm font-medium text-red-950"><CircleAlert aria-hidden="true" className="size-5" /> Memerlukan tindakan</span>
                            <strong className="text-2xl text-red-950">{processing.failed}</strong>
                        </Link>
                    </div>
                </section>
            </div>

            <section className="panel mt-5 rounded-xl p-5" aria-labelledby="trend-heading">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="trend-heading" className="font-semibold">Arah performa</h2>
                        <p className="mt-1 text-xs text-ink-soft">Skor keseluruhan dari analisis yang sudah selesai.</p>
                    </div>
                    <div className="text-sm font-medium text-navy">Rata-rata {stats.average_score || '—'}</div>
                </div>
                {performanceTrend.length ? (
                    <div className="mt-6 flex h-44 items-end gap-3 border-b border-l border-line px-4 pb-3 sm:gap-5">
                        {performanceTrend.map((analysis) => (
                            <Link key={analysis.id} href={`/analyses/${analysis.id}`} className="group flex h-full flex-1 flex-col items-center justify-end gap-2" title={`Skor ${analysis.overall_score}`}>
                                <span className="text-xs font-semibold text-navy opacity-0 transition-opacity group-hover:opacity-100 group-focus:opacity-100">{analysis.overall_score}</span>
                                <span className="w-full max-w-14 rounded-t bg-navy/85 transition-colors group-hover:bg-signal" style={{ height: `${Math.max(12, Number(analysis.overall_score))}%` }} />
                                <span className="text-[0.65rem] text-ink-soft">{formatDate(analysis.match_record?.match_date)}</span>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <div className="mt-6 rounded-lg border border-dashed border-line px-5 py-8 text-center text-sm text-ink-soft">Grafik akan muncul setelah analisis pertama selesai.</div>
                )}
            </section>
        </AppLayout>
    );
}
