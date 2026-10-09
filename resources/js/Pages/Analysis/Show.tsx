import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    ChevronDown,
    Clock3,
    Dumbbell,
    Eye,
    Flag,
    Gauge,
    Radar,
    RotateCcw,
    Shield,
    Sparkles,
    Swords,
    Target,
    TrendingDown,
    TrendingUp,
    Video,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect } from 'react';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { formatTimestamp, matchTeamName, titleCase } from '../../lib/format';
import type { Analysis, AnalysisFinding, AnalysisProfileItem } from '../../types';

export default function AnalysisShow({ analysis, can }: { analysis: Analysis; can: { validate: boolean } }) {
    const youtubeId = getYoutubeId(analysis.video?.external_url);
    const completed = analysis.status === 'completed';
    const canRunAgain = can.validate && ['completed', 'failed'].includes(analysis.status);
    const isEmbu = analysis.match_record?.match_type?.startsWith('embu') ?? false;
    const matchTitle = analysis.match_record ? matchTeamName(analysis.match_record) : (analysis.athlete?.name ?? '');
    const intelligence = analysis.match_intelligence;
    const retryForm = useForm({ focus_description: analysis.video?.focus_description ?? '' });
    const retryAnalysis = () => {
        if (completed && !window.confirm('Jalankan analisis ulang? Hasil AI lama akan diganti setelah proses baru selesai.')) return;

        retryForm.post(`/analyses/${analysis.id}/retry`, { preserveScroll: true });
    };

    useEffect(() => {
        if (['completed', 'failed', 'cancelled'].includes(analysis.status)) return;

        const interval = window.setInterval(() => {
            router.reload({ only: ['analysis'] });
        }, 3000);

        return () => window.clearInterval(interval);
    }, [analysis.status]);

    useEffect(() => {
        const sectionId = window.location.hash.slice(1);

        if (!sectionId) return;

        const frame = window.requestAnimationFrame(() => {
            document.getElementById(sectionId)?.scrollIntoView({ block: 'start' });
        });

        return () => window.cancelAnimationFrame(frame);
    }, []);

    return <AppLayout title={`Analisis ${matchTitle}`} description={analysis.match_record?.category ?? ''} actions={<Link href={`/matches/${analysis.match_record_id}`} className="button-secondary"><ArrowLeft aria-hidden="true" className="size-4" /> Pertandingan</Link>}>
        <Head title={`Analisis ${matchTitle}`} />

        <section className="panel rounded-xl p-4 sm:p-5">
            <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-start gap-4">
                    <div className={`grid size-12 shrink-0 place-items-center rounded-xl ${analysis.status === 'failed' ? 'bg-red-50 text-risk' : completed ? 'bg-emerald-50 text-positive' : 'bg-blue-50 text-navy'}`}>
                        {analysis.status === 'failed' ? <AlertTriangle aria-hidden="true" className="size-6" /> : completed ? <CheckCircle2 aria-hidden="true" className="size-6" /> : <Radar aria-hidden="true" className="size-6" />}
                    </div>
                    <div>
                        <div className="flex flex-wrap items-center gap-2"><h2 className="font-semibold">{analysis.current_step ?? 'Menunggu pembaruan status'}</h2><StatusBadge status={analysis.status} label={titleCase(analysis.status)} /></div>
                        <p className="mt-1 text-sm text-ink-soft">Model {analysis.model_version ?? 'belum ditetapkan'} · ID {analysis.id.slice(-8).toUpperCase()}</p>
                    </div>
                </div>
                <div className="min-w-56">
                    <div className="mb-2 flex justify-between text-xs text-ink-soft"><span>Progres pipeline</span><strong className="text-ink">{analysis.progress}%</strong></div>
                    <div className="h-2.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label="Progres analisis" aria-valuenow={analysis.progress} aria-valuemin={0} aria-valuemax={100}><div className={`h-full rounded-full ${analysis.status === 'failed' ? 'bg-risk' : 'bg-navy'}`} style={{ width: `${analysis.progress}%` }} /></div>
                </div>
            </div>
            {analysis.error_message && <div role="alert" className="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-900">{analysis.error_message}</div>}
            {!completed && analysis.status !== 'failed' && <div className="mt-5 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-950">Pipeline sedang membaca frame, membangun profil pertandingan, mendeteksi peluang, dan menyusun strategi. Hasil sebelumnya tetap terlihat sampai versi baru selesai.</div>}
        </section>

        {completed && intelligence ? <section className="mt-5 overflow-hidden rounded-xl bg-navy-deep text-white shadow-panel">
            <div className="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_260px] lg:p-8">
                <div>
                    <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-blue-200"><Sparkles aria-hidden="true" className="size-4" /> AI Match Intelligence</div>
                    <h2 className="mt-3 max-w-3xl text-2xl font-semibold tracking-[-0.03em]">Ringkasan intelijen pertandingan</h2>
                    <p className="mt-4 max-w-4xl text-base leading-7 text-slate-200">{intelligence.executive_summary || 'Belum ada ringkasan yang cukup kuat dari sampel visual.'}</p>
                    <div className="mt-6 flex flex-wrap gap-2 text-xs">
                        <span className="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">{intelligence.frames_analyzed} frame dianalisis</span>
                        <span className="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">Target: {intelligence.target_identification.label}</span>
                        <span className="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">Keyakinan identitas {toPercent(intelligence.target_identification.confidence)}%</span>
                    </div>
                </div>
                <div className="rounded-xl border border-white/15 bg-white/10 p-5">
                    <div className="text-xs uppercase tracking-[0.14em] text-blue-200">Skor performa visual</div>
                    <div className="mt-3 text-5xl font-semibold tabular-nums">{analysis.overall_score ?? '—'}</div>
                    <p className="mt-3 text-xs leading-5 text-slate-300">Skor AI berdasarkan sampel yang terlihat, bukan nilai resmi juri atau hasil pertandingan.</p>
                </div>
            </div>
            <div className="border-t border-white/10 bg-black/10 px-6 py-4 text-xs leading-5 text-slate-300 lg:px-8"><strong className="text-white">Cakupan:</strong> {intelligence.analysis_scope}</div>
        </section> : completed && <section className="panel mt-4 rounded-xl border-amber-200 bg-amber-50 p-4">
            <div className="flex items-start gap-3"><AlertTriangle aria-hidden="true" className="mt-0.5 size-5 shrink-0 text-signal" /><div><h2 className="font-semibold text-amber-950">Hasil ini memakai format analisis lama</h2><p className="mt-1 text-sm leading-6 text-amber-900">Jalankan ulang agar profil atlet dan lawan, dinamika fase, risiko, strategi lengkap, serta rekomendasi latihan dibuat dalam format AI Match Intelligence.</p></div></div>
        </section>}

        {canRunAgain && <details defaultOpen={analysis.status === 'failed' || Object.keys(retryForm.errors).length > 0} className="group panel mt-4 overflow-hidden rounded-xl">
            <summary className="flex min-h-16 cursor-pointer list-none items-center gap-3 px-4 py-3 select-none hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-navy"><RotateCcw aria-hidden="true" className="size-4" /></span>
                <span className="min-w-0 flex-1"><span className="block text-sm font-semibold">Perbarui target dan analisis ulang</span><span className="mt-0.5 block text-xs leading-5 text-ink-soft">Opsional—gunakan bila atlet atau tim belum teridentifikasi dengan tepat.</span></span>
                <ChevronDown aria-hidden="true" className="size-5 shrink-0 text-ink-soft transition-transform group-open:rotate-180" />
            </summary>
            <div className="border-t border-line bg-slate-50/70 p-4 sm:p-5">
                <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <label htmlFor="analysis-focus-description" className="block">
                        <span className="field-label">Petunjuk atlet atau tim target</span>
                        <textarea id="analysis-focus-description" rows={2} className="field-control resize-y bg-white" aria-describedby="analysis-focus-help" placeholder="Contoh: atlet sudut merah, pelindung merah, berada di kiri saat video dimulai." value={retryForm.data.focus_description} onChange={(event) => retryForm.setData('focus_description', event.target.value)} />
                        <span id="analysis-focus-help" className="mt-2 block text-xs leading-5 text-ink-soft">Isi sudut, warna pelindung, posisi awal, atau ciri lain agar AI tidak tertukar dengan lawan.</span>
                        {retryForm.errors.focus_description && <span role="alert" className="field-error block">{retryForm.errors.focus_description}</span>}
                        {(retryForm.errors as Record<string, string>).analysis && <span role="alert" className="field-error block">{(retryForm.errors as Record<string, string>).analysis}</span>}
                    </label>
                    <button type="button" disabled={retryForm.processing} onClick={retryAnalysis} className="button-secondary min-h-11 shrink-0 disabled:cursor-not-allowed disabled:opacity-50"><RotateCcw aria-hidden="true" className="size-4" /> {retryForm.processing ? 'Mengantrekan…' : completed ? 'Analisis ulang detail' : 'Coba analisis lagi'}</button>
                </div>
            </div>
        </details>}

        <div className="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.85fr)]">
            <section className="panel self-start overflow-hidden rounded-xl">
                <SectionHeader icon={Video} title="Video pertandingan" description="Sumber bukti visual untuk setiap temuan AI." />
                <div className="aspect-video bg-navy-deep">
                    {youtubeId ? <iframe className="h-full w-full" src={`https://www.youtube-nocookie.com/embed/${youtubeId}?rel=0`} title={`Video pertandingan ${analysis.athlete?.name ?? matchTitle}`} allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowFullScreen /> : <div className="grid h-full place-items-center px-6 text-center text-sm text-slate-300">Pratinjau file privat tersedia melalui akses penyimpanan terotorisasi pada tahap integrasi player.</div>}
                </div>
            </section>

            <section id="performance" className="panel self-start scroll-mt-24 overflow-hidden rounded-xl">
                <SectionHeader icon={Gauge} title="Profil performa" description="Skor per dimensi disertai bukti observasi." />
                {analysis.metrics?.length ? <div className="grid gap-4 p-4 sm:p-5">{analysis.metrics.map((metric) => <article key={metric.id}>
                    <div className="mb-2 flex items-start justify-between gap-4"><div><span className="text-xs font-medium uppercase tracking-wide text-ink-soft">{titleCase(metric.category)}</span><h3 className="mt-1 text-sm font-semibold">{metric.name}</h3></div><strong className="tabular-nums text-navy">{metric.score}</strong></div>
                    <div className="h-1.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label={`${metric.name}: ${metric.score} dari 100`} aria-valuenow={Number(metric.score)} aria-valuemin={0} aria-valuemax={100}><div className="h-full rounded-full bg-navy" style={{ width: `${Number(metric.score)}%` }} /></div>
                    <EvidenceList evidence={metric.evidence?.observations} compact />
                </article>)}</div> : <CompactEmptyState icon={Gauge} title="Belum ada metrik yang kuat" description="AI tidak memberi skor jika frame belum cukup untuk menilai performa secara bertanggung jawab." />}
            </section>
        </div>

        {intelligence && <>
            <div className="mt-5 grid gap-5 lg:grid-cols-2">
                <FindingSection icon={TrendingUp} title="Kekuatan utama" items={intelligence.strengths} tone="positive" empty="Belum ada kekuatan yang dapat dibuktikan dari sampel frame." />
                <FindingSection icon={TrendingDown} title="Area perbaikan" items={intelligence.weaknesses} tone="risk" empty="Belum ada kelemahan yang dapat dibuktikan dari sampel frame." />
            </div>

            <div id="opponent" className="mt-5 grid scroll-mt-24 gap-5 lg:grid-cols-2">
                <ProfileSection icon={Swords} title={isEmbu ? 'Profil pasangan / tim' : 'Profil atlet target'} items={intelligence.athlete_profile} empty="Profil target belum dapat disusun dengan keyakinan yang cukup." />
                <ProfileSection icon={Shield} title={isEmbu ? 'Pembacaan formasi' : 'Analisis lawan'} items={intelligence.opponent_profile} empty={isEmbu ? 'Tidak ada profil lawan langsung pada format Embu.' : 'Profil lawan belum dapat disusun dengan keyakinan yang cukup.'} />
            </div>

            <section className="panel mt-5 overflow-hidden rounded-xl">
                <SectionHeader icon={Radar} title="Dinamika pertandingan" description="Perubahan fase dan momentum yang dapat didukung oleh sampel visual." />
                {intelligence.match_dynamics.length ? <div className="divide-y divide-line">{intelligence.match_dynamics.map((phase, index) => <article key={`${phase.phase}-${index}`} className="grid items-start gap-4 p-4 sm:p-5 lg:grid-cols-[150px_minmax(0,1fr)_minmax(0,1fr)]">
                    <div><div className="font-semibold text-navy">{phase.phase}</div><div className="mt-1 text-xs tabular-nums text-ink-soft">{formatTimestamp(phase.start_timestamp_seconds * 1000)}–{formatTimestamp(phase.end_timestamp_seconds * 1000)}</div><div className="mt-3 rounded-md bg-slate-100 px-2.5 py-2 text-xs leading-5"><strong>Momentum:</strong> {phase.momentum}</div></div>
                    <ActionList title={isEmbu ? 'Aksi tim' : 'Aksi target'} items={phase.athlete_actions} />
                    <div><ActionList title={isEmbu ? 'Formasi / pasangan' : 'Aksi lawan'} items={phase.opponent_actions} /><p className="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm leading-6 text-blue-950"><strong>Catatan pelatih:</strong> {phase.coaching_note}</p></div>
                </article>)}</div> : <CompactEmptyState icon={Radar} title="Belum ada fase yang terpetakan" description="Dinamika pertandingan memerlukan beberapa frame yang menunjukkan perubahan situasi." />}
            </section>
        </>}

        <section className="panel mt-5 overflow-hidden rounded-xl">
            <SectionHeader icon={Clock3} title="Timeline observasi" description="Peristiwa visual terurut berdasarkan perkiraan timestamp." />
            {analysis.events?.length ? <div className="divide-y divide-line">{analysis.events.map((event) => <article key={event.id} className="grid items-start gap-3 p-4 sm:p-5 md:grid-cols-[76px_112px_minmax(0,1fr)] md:gap-4">
                <div className="font-semibold tabular-nums text-navy">{formatTimestamp(event.occurred_at_ms)}</div>
                <ConfidenceBadge value={Number(event.confidence)} />
                <div><div className="text-xs font-medium uppercase tracking-wide text-ink-soft">{titleCase(event.event_type)}</div><h3 className="mt-1 font-semibold">{event.title}</h3><p className="mt-2 text-sm leading-6 text-ink-soft">{event.description}</p><EvidenceList evidence={event.evidence?.observations} frameIndex={event.evidence?.frame_index} /></div>
            </article>)}</div> : <CompactEmptyState icon={Clock3} title="Belum ada timeline observasi" description="Peristiwa hanya ditampilkan jika didukung oleh frame dan timestamp yang cukup jelas." />}
        </section>

        <section id="opportunities" className="panel mt-5 scroll-mt-24 overflow-hidden rounded-xl">
            <SectionHeader icon={Target} title="Peluang poin" description="Kandidat peluang untuk divalidasi pelatih, bukan keputusan poin resmi." />
            {analysis.opportunities?.length ? <div className="divide-y divide-line">{analysis.opportunities.map((opportunity) => <article key={opportunity.id} className="grid items-start gap-3 p-4 sm:p-5 md:grid-cols-[76px_112px_minmax(0,1fr)] md:gap-4">
                <div className="font-semibold tabular-nums text-navy">{formatTimestamp(opportunity.occurred_at_ms)}</div>
                <ConfidenceBadge value={Number(opportunity.confidence)} />
                <div><div className="text-xs font-medium uppercase tracking-wide text-ink-soft">{titleCase(opportunity.opportunity_type)}</div><h3 className="mt-1 font-semibold">{opportunity.trigger}</h3><p className="mt-2 text-sm leading-6 text-ink-soft">{opportunity.explanation}</p>{opportunity.recommended_action && <p className="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6"><strong>Tindakan:</strong> {opportunity.recommended_action}</p>}<EvidenceList evidence={opportunity.evidence?.observations} frameIndex={opportunity.evidence?.frame_index} /></div>
            </article>)}</div> : <CompactEmptyState icon={Target} title="Belum ada peluang terdeteksi" description="AI tidak memaksakan peluang poin jika posisi dan konteksnya tidak cukup terlihat." />}
        </section>

        {intelligence?.risk_flags.length ? <section className="panel mt-5 overflow-hidden rounded-xl">
            <SectionHeader icon={AlertTriangle} title="Risiko pertandingan" description="Risiko teknis atau situasional beserta mitigasi yang disarankan." />
            <div className="grid gap-4 p-5 lg:grid-cols-2">{intelligence.risk_flags.map((risk, index) => <article key={`${risk.risk}-${index}`} className="rounded-xl border border-red-100 bg-red-50 p-4"><div className="flex flex-wrap items-center justify-between gap-2"><h3 className="font-semibold text-red-950">{risk.risk}</h3><ConfidenceBadge value={risk.confidence} />{risk.timestamp_seconds !== null && <span className="text-xs tabular-nums text-red-900">{formatTimestamp(risk.timestamp_seconds * 1000)}</span>}</div><p className="mt-2 text-sm leading-6 text-red-900"><strong>Dampak:</strong> {risk.impact}</p><p className="mt-2 text-sm leading-6 text-red-900"><strong>Mitigasi:</strong> {risk.mitigation}</p><EvidenceList evidence={risk.evidence} /></article>)}</div>
        </section> : null}

        <section id="strategy" className="panel mt-5 scroll-mt-24 overflow-hidden rounded-xl">
            <SectionHeader icon={Sparkles} title="Strategi pertandingan" description="Rencana tindakan yang diturunkan dari pola visual dan peluang yang teramati." />
            {analysis.latest_strategy ? <div className="p-4 sm:p-5"><p className="max-w-4xl text-base leading-7">{analysis.latest_strategy.summary}</p>{analysis.latest_strategy.priority_points?.length ? <div className="mt-4 flex flex-wrap gap-2">{analysis.latest_strategy.priority_points.map((point) => <span key={point} className="rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-navy">{point}</span>)}</div> : null}<div className="mt-5 grid items-start gap-4 md:grid-cols-2 xl:grid-cols-4"><StrategyCard title="Serangan" text={analysis.latest_strategy.attack_strategy} /><StrategyCard title="Counter" text={analysis.latest_strategy.counter_strategy} /><StrategyCard title="Pertahanan" text={analysis.latest_strategy.defensive_strategy} /><StrategyCard title="Hindari" text={analysis.latest_strategy.what_to_avoid} tone="risk" /></div></div> : <CompactEmptyState icon={Sparkles} title="Strategi belum tersedia" description="Strategi dibuat setelah pipeline menemukan bukti teknis dan taktis yang memadai." />}
        </section>

        <section id="report" className="panel mt-5 scroll-mt-24 overflow-hidden rounded-xl">
            <SectionHeader icon={Dumbbell} title="Program latihan prioritas" description="Drill yang dapat langsung dimasukkan ke rencana latihan atlet atau tim." />
            {analysis.training_recommendations?.length ? <div className="grid items-start gap-4 p-4 sm:p-5 md:grid-cols-2 xl:grid-cols-3">{analysis.training_recommendations.map((recommendation) => <article key={recommendation.id} className="rounded-xl border border-line p-4"><div className="flex items-center justify-between gap-3"><span className="text-xs font-semibold uppercase tracking-wide text-ink-soft">Prioritas {priorityLabel(recommendation.priority)}</span><Flag aria-hidden="true" className={`size-4 ${recommendation.priority === 'high' ? 'text-risk' : recommendation.priority === 'medium' ? 'text-signal' : 'text-positive'}`} /></div><h3 className="mt-3 font-semibold">{recommendation.drill}</h3><dl className="mt-4 grid grid-cols-2 gap-3 text-sm"><InfoTerm term="Frekuensi" value={recommendation.frequency ?? '—'} /><InfoTerm term="Durasi" value={recommendation.duration_minutes ? `${recommendation.duration_minutes} menit` : '—'} /><InfoTerm term="Target" value={recommendation.target_metric ?? '—'} /><InfoTerm term="Sasaran skor" value={recommendation.target_score ?? '—'} /></dl></article>)}</div> : <CompactEmptyState icon={Dumbbell} title="Belum ada rekomendasi latihan" description="Drill hanya dibuat jika ada temuan yang cukup konkret untuk dilatih." />}
        </section>

        {intelligence && <section className="panel mt-5 rounded-xl p-5">
            <div className="flex items-start gap-3"><Eye aria-hidden="true" className="mt-0.5 size-5 shrink-0 text-navy" /><div><h2 className="font-semibold">Identifikasi dan keterbatasan analisis</h2><p className="mt-2 text-sm leading-6 text-ink-soft"><strong>Dasar identifikasi:</strong> {intelligence.target_identification.basis}</p>{intelligence.target_identification.caveat && <p className="mt-2 text-sm leading-6 text-ink-soft"><strong>Catatan:</strong> {intelligence.target_identification.caveat}</p>}{intelligence.limitations.length ? <ul className="mt-3 list-disc space-y-1 pl-5 text-sm leading-6 text-ink-soft">{intelligence.limitations.map((item, index) => <li key={`${item}-${index}`}>{item}</li>)}</ul> : null}</div></div>
        </section>}
    </AppLayout>;
}

function SectionHeader({ icon: Icon, title, description }: { icon: LucideIcon; title: string; description: string }) {
    return <div className="flex items-start justify-between gap-4 border-b border-line px-4 py-3.5 sm:px-5 sm:py-4"><div className="min-w-0"><h2 className="font-semibold">{title}</h2><p className="mt-1 text-xs leading-5 text-ink-soft">{description}</p></div><Icon aria-hidden="true" className="mt-0.5 size-5 shrink-0 text-navy" /></div>;
}

function CompactEmptyState({ icon: Icon, title, description }: { icon: LucideIcon; title: string; description: string }) {
    return <div className="flex flex-col items-center justify-center px-5 py-9 text-center"><div className="grid size-10 place-items-center rounded-full bg-slate-100 text-navy"><Icon aria-hidden="true" className="size-4" /></div><h3 className="mt-3 text-sm font-semibold">{title}</h3><p className="mt-1.5 max-w-md text-sm leading-6 text-ink-soft">{description}</p></div>;
}

function FindingSection({ icon: Icon, title, items, tone, empty }: { icon: LucideIcon; title: string; items: AnalysisFinding[]; tone: 'positive' | 'risk'; empty: string }) {
    return <section className="panel overflow-hidden rounded-xl"><SectionHeader icon={Icon} title={title} description={tone === 'positive' ? 'Pola yang layak dipertahankan dan dikembangkan.' : 'Pola yang perlu diperbaiki atau dikendalikan.'} />{items.length ? <div className="grid gap-4 p-4 sm:p-5">{items.map((item, index) => <article key={`${item.title}-${index}`} className={`rounded-xl border p-4 ${tone === 'positive' ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50'}`}><div className="flex flex-wrap items-start justify-between gap-2"><h3 className="font-semibold">{item.title}</h3><ConfidenceBadge value={item.confidence} /></div><p className="mt-2 text-sm leading-6 text-ink-soft">{item.detail}</p><EvidenceList evidence={item.evidence} /></article>)}</div> : <p className="p-4 text-sm leading-6 text-ink-soft sm:p-5">{empty}</p>}</section>;
}

function ProfileSection({ icon, title, items, empty }: { icon: LucideIcon; title: string; items: AnalysisProfileItem[]; empty: string }) {
    return <section className="panel overflow-hidden rounded-xl"><SectionHeader icon={icon} title={title} description="Profil per aspek berdasarkan bukti visual yang tersedia." />{items.length ? <div className="divide-y divide-line">{items.map((item, index) => <article key={`${item.aspect}-${index}`} className="p-4 sm:p-5"><div className="flex flex-wrap items-start justify-between gap-2"><h3 className="font-semibold">{item.aspect}</h3><ConfidenceBadge value={item.confidence} /></div><p className="mt-2 text-sm leading-6 text-ink-soft">{item.assessment}</p><EvidenceList evidence={item.evidence} /></article>)}</div> : <p className="p-4 text-sm leading-6 text-ink-soft sm:p-5">{empty}</p>}</section>;
}

function ConfidenceBadge({ value }: { value: number }) {
    const percentage = toPercent(value);
    const level = percentage >= 75 ? 'Tinggi' : percentage >= 50 ? 'Sedang' : 'Rendah';
    const classes = percentage >= 75 ? 'bg-emerald-50 text-emerald-800' : percentage >= 50 ? 'bg-amber-50 text-amber-900' : 'bg-slate-100 text-slate-700';

    return <span className={`inline-flex w-fit shrink-0 self-start items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ${classes}`}>{percentage}% · {level}</span>;
}

function EvidenceList({ evidence, frameIndex, compact = false }: { evidence?: string[]; frameIndex?: number; compact?: boolean }) {
    if (!evidence?.length) return null;

    return <details className={`group/evidence ${compact ? 'mt-2' : 'mt-3'} text-xs text-ink-soft`}><summary className="flex min-h-8 w-fit cursor-pointer list-none items-center gap-1.5 rounded-md px-1 py-1 font-semibold text-navy hover:bg-blue-50 [&::-webkit-details-marker]:hidden"><span>Bukti visual{frameIndex ? ` · frame ${frameIndex}` : ''}</span><ChevronDown aria-hidden="true" className="size-3.5 shrink-0 transition-transform group-open/evidence:rotate-180" /></summary><ul className="mt-1 list-disc space-y-1 pl-5 leading-5">{evidence.map((item, index) => <li key={`${item}-${index}`}>{item}</li>)}</ul></details>;
}

function ActionList({ title, items }: { title: string; items: string[] }) {
    return <div><h3 className="text-xs font-semibold uppercase tracking-wide text-ink-soft">{title}</h3>{items.length ? <ul className="mt-2 list-disc space-y-1.5 pl-5 text-sm leading-6">{items.map((item, index) => <li key={`${item}-${index}`}>{item}</li>)}</ul> : <p className="mt-2 text-sm text-ink-soft">Tidak cukup bukti.</p>}</div>;
}

function StrategyCard({ title, text, tone = 'default' }: { title: string; text: string; tone?: 'default' | 'risk' }) {
    return <article className={`rounded-xl border p-4 ${tone === 'risk' ? 'border-red-100 bg-red-50' : 'border-line bg-slate-50'}`}><h3 className="text-sm font-semibold">{title}</h3><p className="mt-2 text-sm leading-6 text-ink-soft">{text}</p></article>;
}

function InfoTerm({ term, value }: { term: string; value: string | number }) {
    return <div><dt className="text-xs text-ink-soft">{term}</dt><dd className="mt-1 font-medium">{value}</dd></div>;
}

function priorityLabel(priority: 'high' | 'medium' | 'low'): string {
    return priority === 'high' ? 'tinggi' : priority === 'medium' ? 'sedang' : 'rendah';
}

function toPercent(value: number): number {
    return Math.round(Number(value) * 100);
}

function getYoutubeId(url?: string | null): string | null {
    if (!url) return null;

    try {
        const parsed = new URL(url);

        if (parsed.hostname === 'youtu.be') return parsed.pathname.slice(1).split('/')[0] || null;

        return parsed.searchParams.get('v');
    } catch {
        return null;
    }
}
