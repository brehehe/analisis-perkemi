import { Head, Link, router } from '@inertiajs/react';
import { Activity, FileSearch, Video } from 'lucide-react';
import EmptyState from '../../Components/EmptyState';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { formatDateTime, matchTeamName, titleCase } from '../../lib/format';
import type { Analysis, Option, Paginated } from '../../types';

export default function AnalysisIndex({ analyses, filter, statusOptions }: { analyses: Paginated<Analysis>; filter: string; statusOptions: Option[] }) {
    return <AppLayout title="Antrean analisis" description="Pantau validasi video, progres worker, kegagalan, dan hasil yang siap ditinjau pelatih.">
        <Head title="Antrean analisis" />
        <section className="panel overflow-hidden rounded-xl">
            <div className="flex flex-col gap-3 border-b border-line bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3"><div className="grid size-10 place-items-center rounded-lg bg-blue-50 text-navy"><Activity aria-hidden="true" className="size-5" /></div><div><div className="text-sm font-semibold">Status pekerjaan</div><div className="text-xs text-ink-soft">Filter berdasarkan tahap pipeline.</div></div></div>
                <label className="sm:w-64"><span className="sr-only">Status analisis</span><select className="field-control" value={filter} onChange={(e) => router.get('/analyses', { status: e.target.value }, { preserveState: true, replace: true })}><option value="">Semua status</option><option value="active">Sedang diproses</option>{statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select></label>
            </div>
            {analyses.data.length ? <>
                <div className="divide-y divide-line">
                    {analyses.data.map((analysis) => <Link key={analysis.id} href={`/analyses/${analysis.id}`} className="grid gap-4 px-5 py-5 hover:bg-slate-50 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_150px] sm:items-center">
                        <div className="flex min-w-0 items-start gap-3"><div className="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-navy"><Video aria-hidden="true" className="size-5" /></div><div className="min-w-0"><div className="truncate font-semibold text-navy">{analysis.match_record ? matchTeamName(analysis.match_record) : analysis.athlete?.name}</div><div className="mt-1 truncate text-xs text-ink-soft">{analysis.match_record?.category} · {formatDateTime(analysis.match_record?.match_date)}</div></div></div>
                        <div><div className="mb-2 flex items-center justify-between text-xs"><span className="truncate text-ink-soft">{analysis.current_step ?? 'Menunggu pembaruan'}</span><strong>{analysis.progress}%</strong></div><div className="h-2 overflow-hidden rounded-full bg-slate-200"><div className={`h-full ${analysis.status === 'failed' ? 'bg-risk' : 'bg-navy'}`} style={{ width: `${analysis.progress}%` }} /></div></div>
                        <div className="flex items-center justify-between gap-3 sm:justify-end"><StatusBadge status={analysis.status} label={titleCase(analysis.status)} />{analysis.overall_score && <span className="text-xl font-semibold">{analysis.overall_score}</span>}</div>
                    </Link>)}
                </div>
                <Pagination paginator={analyses} />
            </> : <EmptyState icon={FileSearch} title="Tidak ada analisis" description={filter ? 'Tidak ada pekerjaan dengan status tersebut. Pilih status lain.' : 'Tambahkan video pada pertandingan untuk membuat pekerjaan analisis.'} action={!filter ? <Link href="/matches" className="button-primary">Pilih pertandingan</Link> : undefined} />}
        </section>
    </AppLayout>;
}
