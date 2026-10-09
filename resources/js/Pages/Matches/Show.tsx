import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowUpRight, FileVideo, Pencil, PlayCircle, Trash2, Upload } from 'lucide-react';
import { useEffect, useState } from 'react';
import EmptyState from '../../Components/EmptyState';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { formatDateTime, matchTeamName, titleCase } from '../../lib/format';
import type { MatchRecord } from '../../types';

type VideoForm = { source: 'youtube' | 'upload'; youtube_url: string; video_file: File | null; focus_description: string };
type MatchTab = 'overview' | 'video';

export default function MatchShow({ match, can }: { match: MatchRecord; can: { update: boolean; delete: boolean; uploadVideo: boolean } }) {
    const form = useForm<VideoForm>({ source: 'youtube', youtube_url: '', video_file: null, focus_description: '' });
    const latestAnalysis = match.analyses?.[0];
    const isEmbu = match.match_type?.startsWith('embu') ?? false;
    const teamName = match.athletes?.map((athlete) => athlete.name).join(', ') || match.athlete?.name || '—';
    const pageTitle = matchTeamName(match);
    const [activeTab, setActiveTab] = useState<MatchTab>(() => typeof window !== 'undefined' && window.location.hash === '#video' ? 'video' : 'overview');
    const submitVideo = (event: React.FormEvent<HTMLFormElement>) => { event.preventDefault(); form.post(`/matches/${match.id}/videos`, { forceFormData: true }); };
    const archive = () => { if (window.confirm('Arsipkan pertandingan ini? Histori analisis tetap tersimpan.')) router.delete(`/matches/${match.id}`); };
    const selectTab = (tab: MatchTab) => {
        setActiveTab(tab);
        window.history.pushState({}, '', `#${tab}`);
    };

    useEffect(() => {
        const syncTabWithHash = () => setActiveTab(window.location.hash === '#video' ? 'video' : 'overview');

        window.addEventListener('hashchange', syncTabWithHash);
        window.addEventListener('popstate', syncTabWithHash);

        return () => {
            window.removeEventListener('hashchange', syncTabWithHash);
            window.removeEventListener('popstate', syncTabWithHash);
        };
    }, []);

    return <AppLayout title={pageTitle} description={`${match.category} · ${formatDateTime(match.match_date)}`} actions={<>{can.update && <Link href={`/matches/${match.id}/edit`} className="button-secondary"><Pencil aria-hidden="true" className="size-4" /> Ubah</Link>}{can.delete && <button type="button" onClick={archive} className="button-secondary text-risk"><Trash2 aria-hidden="true" className="size-4" /> Arsipkan</button>}{latestAnalysis && <Link href={`/analyses/${latestAnalysis.id}`} className="button-primary">Buka analisis <ArrowUpRight aria-hidden="true" className="size-4" /></Link>}</>}>
        <Head title={pageTitle} />

        <nav aria-label="Bagian pertandingan" className="mb-5 flex gap-1 overflow-x-auto border-b border-line pb-px text-sm font-medium">
            <button type="button" aria-current={activeTab === 'overview' ? 'page' : undefined} onClick={() => selectTab('overview')} className={tabClass(activeTab === 'overview')}>Ringkasan</button>
            <button type="button" aria-current={activeTab === 'video' ? 'page' : undefined} onClick={() => selectTab('video')} className={tabClass(activeTab === 'video')}>Video</button>
            {[
                ['performance', 'Performa'],
                ['opponent', isEmbu ? 'Tim' : 'Lawan'],
                ['opportunities', 'Peluang poin'],
                ['strategy', 'Strategi'],
                ['report', 'Laporan'],
            ].map(([section, label]) => latestAnalysis
                ? <Link key={section} href={`/analyses/${latestAnalysis.id}#${section}`} className={tabClass(false)}>{label}</Link>
                : <span key={section} aria-disabled="true" className="min-h-11 shrink-0 cursor-not-allowed border-b-2 border-transparent px-3 py-2.5 text-slate-400">{label}</span>)}
        </nav>

        {activeTab === 'overview' && <section id="overview" aria-label="Ringkasan pertandingan" className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Info label={isEmbu ? 'Tim Embu' : 'Sudut merah'} value={teamName} detail={isEmbu ? `${match.athletes?.length ?? 1} atlet` : match.athlete?.identifier} />
            <Info label={isEmbu ? 'Format' : 'Sudut biru'} value={isEmbu ? match.category : (match.opponent_name ?? '—')} detail={isEmbu ? 'Tanpa lawan langsung' : match.opponent_club} />
            <Info label="Event" value={match.competition_event?.name ?? 'Pertandingan mandiri'} detail={match.competition_event?.venue} />
            <div className="panel rounded-xl p-5"><div className="text-xs text-ink-soft">Hasil / status</div><div className="mt-3 flex flex-wrap gap-2"><StatusBadge status={match.result} label={titleCase(match.result)} /><StatusBadge status={match.status} label={titleCase(match.status)} /></div>{match.athlete_score != null && <div className="mt-3 text-sm font-semibold">{isEmbu ? `Nilai ${match.athlete_score}` : `Skor ${match.athlete_score}–${match.opponent_score ?? 0}`}</div>}</div>
        </section>}

        {activeTab === 'video' && <div id="video" aria-label="Video dan riwayat analisis" className="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.85fr)]">
            <section id="analysis" className="panel overflow-hidden rounded-xl">
                <div className="border-b border-line px-5 py-4"><h2 className="font-semibold">Riwayat analisis</h2><p className="mt-1 text-xs text-ink-soft">Setiap video memiliki job dan hasil analisis terpisah.</p></div>
                {match.analyses?.length ? <div className="divide-y divide-line">{match.analyses.map((analysis) => <Link key={analysis.id} href={`/analyses/${analysis.id}`} className="flex min-h-20 items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50"><div className="min-w-0"><div className="flex items-center gap-2"><PlayCircle aria-hidden="true" className="size-4 shrink-0 text-navy" /><span className="truncate font-semibold text-navy">Analisis {analysis.id.slice(-6).toUpperCase()}</span></div><div className="mt-1 truncate text-xs text-ink-soft">{analysis.current_step ?? 'Menunggu pembaruan status'} · progres {analysis.progress}%</div></div><StatusBadge status={analysis.status} label={titleCase(analysis.status)} /></Link>)}</div> : <EmptyState icon={FileVideo} title="Belum ada analisis" description="Tambahkan video pertandingan untuk membuat job analisis pertama." />}
            </section>

            <section className="panel rounded-xl p-5">
                <div className="flex items-start justify-between"><div><h2 className="font-semibold">Tambahkan video</h2><p className="mt-1 text-xs leading-5 text-ink-soft">Video disimpan privat dan diproses melalui antrean.</p></div><Upload aria-hidden="true" className="size-5 text-navy" /></div>
                {can.uploadVideo ? <form onSubmit={submitVideo} className="mt-5 space-y-4" noValidate>
                    <fieldset><legend className="field-label">Sumber video</legend><div className="grid grid-cols-2 gap-2"><button type="button" aria-pressed={form.data.source === 'youtube'} onClick={() => form.setData('source', 'youtube')} className={`min-h-12 rounded-lg border px-3 text-sm font-semibold ${form.data.source === 'youtube' ? 'border-navy bg-blue-50 text-navy' : 'border-line bg-white text-ink-soft'}`}><PlayCircle aria-hidden="true" className="mr-2 inline size-4" /> YouTube</button><button type="button" aria-pressed={form.data.source === 'upload'} onClick={() => form.setData('source', 'upload')} className={`min-h-12 rounded-lg border px-3 text-sm font-semibold ${form.data.source === 'upload' ? 'border-navy bg-blue-50 text-navy' : 'border-line bg-white text-ink-soft'}`}><FileVideo aria-hidden="true" className="mr-2 inline size-4" /> Unggah</button></div></fieldset>
                    {form.data.source === 'youtube' ? <label className="block"><span className="field-label">URL YouTube</span><input type="url" className="field-control" placeholder="https://youtube.com/watch?v=…" value={form.data.youtube_url} onChange={(e) => form.setData('youtube_url', e.target.value)} />{form.errors.youtube_url && <span className="field-error block">{form.errors.youtube_url}</span>}</label> : <label className="block"><span className="field-label">File video</span><input type="file" accept=".mp4,.mov,.mkv,.webm,video/*" className="field-control file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold" onChange={(e) => form.setData('video_file', e.target.files?.[0] ?? null)} /><span className="mt-2 block text-xs text-ink-soft">MP4, MOV, MKV, atau WebM. Maksimal 500 MB.</span>{form.errors.video_file && <span className="field-error block">{form.errors.video_file}</span>}</label>}
                    <label className="block" htmlFor="focus-description"><span className="field-label">Ciri atlet atau tim yang dianalisis</span><textarea id="focus-description" rows={3} className="field-control resize-y" placeholder="Contoh: atlet sudut merah, pelindung kepala merah, berada di kiri saat video dimulai." aria-describedby="focus-description-help" value={form.data.focus_description} onChange={(e) => form.setData('focus_description', e.target.value)} /><span id="focus-description-help" className="mt-2 block text-xs leading-5 text-ink-soft">Sangat disarankan agar AI tidak tertukar antara atlet target dan lawan. Untuk Embu, jelaskan pasangan atau posisi tim.</span>{form.errors.focus_description && <span role="alert" className="field-error block">{form.errors.focus_description}</span>}</label>
                    <button type="submit" disabled={form.processing} className="button-primary w-full"><Upload aria-hidden="true" className="size-4" /> {form.processing ? 'Mengirim video…' : 'Kirim ke antrean'}</button>
                    {form.progress && <div aria-label={`Progres unggah ${form.progress.percentage}%`} className="h-2 overflow-hidden rounded-full bg-slate-200"><div className="h-full bg-navy" style={{ width: `${form.progress.percentage}%` }} /></div>}
                </form> : <p className="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-ink-soft">Akun Anda tidak memiliki izin mengunggah video.</p>}
            </section>
        </div>}
    </AppLayout>;
}

function tabClass(active: boolean): string {
    return `min-h-11 shrink-0 border-b-2 px-3 py-2.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy ${active ? 'border-navy text-navy' : 'border-transparent text-ink-soft hover:text-ink'}`;
}

function Info({ label, value, detail }: { label: string; value: string; detail?: string | null }) { return <div className="panel rounded-xl p-5"><div className="text-xs text-ink-soft">{label}</div><div className="mt-2 font-semibold">{value}</div>{detail && <div className="mt-1 text-xs text-ink-soft">{detail}</div>}</div>; }
