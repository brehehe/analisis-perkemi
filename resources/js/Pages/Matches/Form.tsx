import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Info, Save, Users } from 'lucide-react';
import AppLayout from '../../Layouts/AppLayout';
import { titleCase } from '../../lib/format';
import type { Athlete, CompetitionEvent, MatchRecord, Option } from '../../types';

type MatchTypeOption = Option & { team_size: number; has_opponent: boolean; allows_mixed: boolean };
type Division = 'male' | 'female' | 'mixed';
type Props = { match?: MatchRecord; athletes: Athlete[]; events: CompetitionEvent[]; resultOptions: string[]; statusOptions: string[]; matchTypeOptions: MatchTypeOption[]; divisionOptions: Option[] };
type MatchForm = { competition_event_id: string; athlete_ids: string[]; opponent_name: string; opponent_club: string; match_date: string; match_type: string; division: Division; result: string; athlete_score: string; opponent_score: string; status: string; notes: string };

export default function MatchFormPage({ match, athletes, events, resultOptions, statusOptions, matchTypeOptions, divisionOptions }: Props) {
    const initialMatchType = match?.match_type ?? 'randori';
    const initialTypeOption = matchTypeOptions.find((option) => option.value === initialMatchType) ?? matchTypeOptions[0];
    const initialAthleteIds = match?.athletes?.map((athlete) => athlete.id) ?? (match?.athlete_id ? [match.athlete_id] : []);
    const form = useForm<MatchForm>({
        competition_event_id: match?.competition_event_id ?? '', athlete_ids: resizeTeam(initialAthleteIds, initialTypeOption?.team_size ?? 1), opponent_name: match?.opponent_name ?? '', opponent_club: match?.opponent_club ?? '',
        match_date: match?.match_date ? match.match_date.slice(0, 16) : '', match_type: initialMatchType, division: match?.division ?? 'male', result: match?.result ?? 'pending',
        athlete_score: match?.athlete_score == null ? '' : String(match.athlete_score), opponent_score: match?.opponent_score == null ? '' : String(match.opponent_score), status: match?.status ?? 'scheduled', notes: match?.notes ?? '',
    });
    const submit = (event: React.FormEvent<HTMLFormElement>) => { event.preventDefault(); match ? form.put(`/matches/${match.id}`) : form.post('/matches'); };
    const selectedType = matchTypeOptions.find((option) => option.value === form.data.match_type) ?? matchTypeOptions[0];
    const selectedDivision = divisionOptions.find((option) => option.value === form.data.division);
    const eligibleAthletes = form.data.division === 'mixed' ? athletes : athletes.filter((athlete) => athlete.gender === form.data.division);
    const category = `${selectedType?.label ?? ''} ${selectedDivision?.label ?? ''}`.trim();
    const errors = form.errors as Record<string, string>;

    const setMatchType = (matchType: MatchTypeOption) => {
        form.setData((data) => ({
            ...data,
            match_type: matchType.value,
            division: !matchType.allows_mixed && data.division === 'mixed' ? 'male' : data.division,
            athlete_ids: resizeTeam(data.athlete_ids, matchType.team_size),
            opponent_name: matchType.has_opponent ? data.opponent_name : '',
            opponent_club: matchType.has_opponent ? data.opponent_club : '',
            opponent_score: matchType.has_opponent ? data.opponent_score : '',
        }));
    };

    const setDivision = (division: Division) => {
        form.setData((data) => ({ ...data, division, athlete_ids: resizeTeam([], selectedType?.team_size ?? 1) }));
    };

    const setAthlete = (index: number, athleteId: string) => {
        const athleteIds = [...form.data.athlete_ids];
        athleteIds[index] = athleteId;
        form.setData('athlete_ids', athleteIds);
    };

    return <AppLayout title={match ? 'Ubah pertandingan' : 'Buat pertandingan'} description="Simpan konteks pertandingan terlebih dahulu sebelum video dianalisis." actions={<Link href={match ? `/matches/${match.id}` : '/matches'} className="button-secondary"><ArrowLeft aria-hidden="true" className="size-4" /> Kembali</Link>}>
        <Head title={match ? 'Ubah pertandingan' : 'Buat pertandingan'} />
        <form onSubmit={submit} className="panel rounded-xl" noValidate>
            <div className="border-b border-line px-5 py-4 sm:px-6"><h2 className="font-semibold">Format pertandingan</h2><p className="mt-1 text-xs text-ink-soft">Randori mempertemukan merah dan biru. Embu dinilai sebagai satu tim tanpa lawan langsung.</p></div>
            <div className="grid gap-6 p-5 sm:p-6">
                <fieldset>
                    <legend className="field-label">Jenis pertandingan <span aria-hidden="true" className="text-risk">*</span></legend>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        {matchTypeOptions.map((option) => <button key={option.value} type="button" aria-pressed={form.data.match_type === option.value} onClick={() => setMatchType(option)} className={`min-h-24 rounded-xl border p-4 text-left transition-colors ${form.data.match_type === option.value ? 'border-navy bg-blue-50 text-navy' : 'border-line bg-white text-ink hover:border-slate-400'}`}><span className="block font-semibold">{option.label}</span><span className="mt-1.5 block text-xs text-ink-soft">{option.team_size} atlet {option.has_opponent ? '· merah vs biru' : '· satu tim'}</span></button>)}
                    </div>
                    {form.errors.match_type && <span className="field-error block">{form.errors.match_type}</span>}
                </fieldset>

                <fieldset>
                    <legend className="field-label">Kategori peserta <span aria-hidden="true" className="text-risk">*</span></legend>
                    <div className="grid max-w-xl grid-cols-3 gap-2">
                        {divisionOptions.map((option) => {
                            const disabled = option.value === 'mixed' && !selectedType?.allows_mixed;
                            return <button key={option.value} type="button" disabled={disabled} aria-pressed={form.data.division === option.value} onClick={() => setDivision(option.value as Division)} className={`min-h-12 rounded-lg border px-3 text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 ${form.data.division === option.value ? 'border-navy bg-navy text-white' : 'border-line bg-white text-ink'}`}>{option.label}</button>;
                        })}
                    </div>
                    {form.errors.division && <span className="field-error block">{form.errors.division}</span>}
                </fieldset>

                <fieldset className="rounded-xl border border-line bg-slate-50/70 p-4 sm:p-5">
                    <legend className="px-2 font-semibold text-ink">{selectedType?.team_size === 1 ? 'Atlet peserta' : `Anggota tim · ${selectedType?.team_size} atlet`}</legend>
                    <div className="grid gap-4 sm:grid-cols-2">
                        {form.data.athlete_ids.map((athleteId, index) => <Field key={index} label={selectedType?.team_size === 1 ? 'Atlet' : `Anggota ${index + 1}`} error={errors[`athlete_ids.${index}`]} required><select className="field-control bg-white" value={athleteId} onChange={(event) => setAthlete(index, event.target.value)}><option value="">Pilih atlet</option>{eligibleAthletes.map((athlete) => <option key={athlete.id} value={athlete.id} disabled={form.data.athlete_ids.some((selectedId, selectedIndex) => selectedIndex !== index && selectedId === athlete.id)}>{athlete.name} · {athlete.identifier} · {athlete.gender === 'male' ? 'Putra' : 'Putri'}</option>)}</select></Field>)}
                    </div>
                    {form.errors.athlete_ids && <span className="field-error block">{form.errors.athlete_ids}</span>}
                    {form.data.division === 'mixed' && <p className="mt-3 flex items-center gap-2 text-xs text-ink-soft"><Users aria-hidden="true" className="size-4" /> Tim Campuran harus memuat atlet putra dan putri.</p>}
                </fieldset>

                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Event" error={form.errors.competition_event_id}><select className="field-control" value={form.data.competition_event_id} onChange={(e) => form.setData('competition_event_id', e.target.value)}><option value="">Pertandingan mandiri</option>{events.map((event) => <option key={event.id} value={event.id}>{event.name}</option>)}</select></Field>
                    <Field label="Tanggal dan waktu" error={form.errors.match_date} required><input type="datetime-local" className="field-control" value={form.data.match_date} onChange={(e) => form.setData('match_date', e.target.value)} /></Field>
                </div>

                <div className="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-950"><strong>Kategori:</strong> {category}</div>

                {selectedType?.has_opponent ? <div className="grid gap-5 sm:grid-cols-2"><Field label="Nama lawan / sudut biru" error={form.errors.opponent_name} required><input className="field-control" value={form.data.opponent_name} onChange={(e) => form.setData('opponent_name', e.target.value)} /></Field><Field label="Klub lawan" error={form.errors.opponent_club}><input className="field-control" value={form.data.opponent_club} onChange={(e) => form.setData('opponent_club', e.target.value)} /></Field></div> : <div className="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950"><Info aria-hidden="true" className="mt-0.5 size-4 shrink-0" /><span>Embu dicatat sebagai penampilan satu tim, sehingga nama dan skor lawan tidak diperlukan.</span></div>}

                <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Status" error={form.errors.status} required><select className="field-control" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>{statusOptions.map((option) => <option key={option} value={option}>{titleCase(option)}</option>)}</select></Field>
                <Field label="Hasil" error={form.errors.result} required><select className="field-control" value={form.data.result} onChange={(e) => form.setData('result', e.target.value)}>{resultOptions.map((option) => <option key={option} value={option}>{titleCase(option)}</option>)}</select></Field>
                </div>

                <div className={`grid gap-3 ${selectedType?.has_opponent ? 'grid-cols-2' : 'sm:grid-cols-2'}`}><Field label={selectedType?.has_opponent ? 'Skor sudut merah' : 'Nilai tim'} error={form.errors.athlete_score}><input type="number" min="0" className="field-control" value={form.data.athlete_score} onChange={(e) => form.setData('athlete_score', e.target.value)} /></Field>{selectedType?.has_opponent && <Field label="Skor sudut biru" error={form.errors.opponent_score}><input type="number" min="0" className="field-control" value={form.data.opponent_score} onChange={(e) => form.setData('opponent_score', e.target.value)} /></Field>}</div>
                <label className="block"><span className="field-label">Catatan</span><textarea className="field-control min-h-28" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />{form.errors.notes && <span className="field-error block">{form.errors.notes}</span>}</label>
            </div>
            <div className="flex justify-end gap-3 border-t border-line bg-slate-50/70 px-5 py-4 sm:px-6"><Link href={match ? `/matches/${match.id}` : '/matches'} className="button-secondary">Batal</Link><button type="submit" disabled={form.processing} className="button-primary"><Save aria-hidden="true" className="size-4" /> {form.processing ? 'Menyimpan…' : 'Simpan pertandingan'}</button></div>
        </form>
    </AppLayout>;
}

function Field({ label, error, required, children }: { label: string; error?: string; required?: boolean; children: React.ReactElement }) { return <label className="block"><span className="field-label">{label}{required && <span aria-hidden="true" className="text-risk"> *</span>}</span>{children}{error && <span className="field-error block">{error}</span>}</label>; }

function resizeTeam(athleteIds: string[], size: number): string[] {
    return Array.from({ length: size }, (_, index) => athleteIds[index] ?? '');
}
