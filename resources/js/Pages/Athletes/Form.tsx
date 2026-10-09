import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AppLayout from '../../Layouts/AppLayout';
import type { Athlete, Club, Coach, Option } from '../../types';

type Props = {
    athlete?: Athlete;
    clubs: Club[];
    coaches: Coach[];
    genderOptions: Option[];
    statusOptions: string[];
};

type AthleteForm = {
    club_id: string;
    coach_id: string;
    identifier: string;
    name: string;
    gender: string;
    date_of_birth: string;
    category: string;
    weight_class: string;
    experience_years: string;
    status: string;
};

export default function AthleteFormPage({ athlete, clubs, coaches, genderOptions, statusOptions }: Props) {
    const editing = Boolean(athlete);
    const form = useForm<AthleteForm>({
        club_id: athlete?.club_id ?? '',
        coach_id: athlete?.coach_id ?? '',
        identifier: athlete?.identifier ?? '',
        name: athlete?.name ?? '',
        gender: athlete?.gender ?? 'male',
        date_of_birth: athlete?.date_of_birth?.slice(0, 10) ?? '',
        category: athlete?.category ?? '',
        weight_class: athlete?.weight_class ?? '',
        experience_years: String(athlete?.experience_years ?? 0),
        status: athlete?.status ?? 'active',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (athlete) form.put(`/athletes/${athlete.id}`);
        else form.post('/athletes');
    };

    const errorSummary = Object.values(form.errors);

    return (
        <AppLayout title={editing ? 'Ubah data atlet' : 'Tambah atlet'} description="Identitas yang lengkap membuat analisis lintas pertandingan lebih akurat dan mudah ditelusuri." actions={<Link href={athlete ? `/athletes/${athlete.id}` : '/athletes'} className="button-secondary"><ArrowLeft aria-hidden="true" className="size-4" /> Kembali</Link>}>
            <Head title={editing ? 'Ubah atlet' : 'Tambah atlet'} />

            <form onSubmit={submit} className="panel rounded-xl" noValidate>
                {errorSummary.length > 1 && (
                    <div role="alert" className="m-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                        Periksa kembali {errorSummary.length} isian yang ditandai.
                    </div>
                )}

                <div className="border-b border-line px-5 py-4 sm:px-6">
                    <h2 className="font-semibold">Identitas dan klasifikasi</h2>
                    <p className="mt-1 text-xs text-ink-soft">Kolom bertanda wajib harus diisi.</p>
                </div>

                <div className="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <Field label="NIK / identitas" error={form.errors.identifier} required>
                        <input className="field-control" value={form.data.identifier} onChange={(e) => form.setData('identifier', e.target.value)} autoComplete="off" />
                    </Field>
                    <Field label="Nama lengkap" error={form.errors.name} required>
                        <input className="field-control" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} autoComplete="name" />
                    </Field>
                    <Field label="Jenis kelamin" error={form.errors.gender} required>
                        <select className="field-control" value={form.data.gender} onChange={(e) => form.setData('gender', e.target.value)}>{genderOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select>
                    </Field>
                    <Field label="Tanggal lahir" error={form.errors.date_of_birth}>
                        <input type="date" className="field-control" value={form.data.date_of_birth} onChange={(e) => form.setData('date_of_birth', e.target.value)} />
                    </Field>
                    <Field label="Kategori" error={form.errors.category} required>
                        <input className="field-control" value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} placeholder="Contoh: Randori Putra" />
                    </Field>
                    <Field label="Kelas berat (kg)" error={form.errors.weight_class}>
                        <input type="number" min="20" max="250" step="0.01" className="field-control" value={form.data.weight_class} onChange={(e) => form.setData('weight_class', e.target.value)} />
                    </Field>
                    <Field label="Klub" error={form.errors.club_id}>
                        <select className="field-control" value={form.data.club_id} onChange={(e) => form.setData('club_id', e.target.value)}><option value="">Pilih klub</option>{clubs.map((club) => <option key={club.id} value={club.id}>{club.name}</option>)}</select>
                    </Field>
                    <Field label="Pelatih" error={form.errors.coach_id}>
                        <select className="field-control" value={form.data.coach_id} onChange={(e) => form.setData('coach_id', e.target.value)}><option value="">Pilih pelatih</option>{coaches.filter((coach) => !form.data.club_id || !coach.club_id || coach.club_id === form.data.club_id).map((coach) => <option key={coach.id} value={coach.id}>{coach.name}</option>)}</select>
                    </Field>
                    <Field label="Pengalaman (tahun)" error={form.errors.experience_years} required>
                        <input type="number" min="0" max="80" className="field-control" value={form.data.experience_years} onChange={(e) => form.setData('experience_years', e.target.value)} />
                    </Field>
                    <Field label="Status" error={form.errors.status} required>
                        <select className="field-control" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>{statusOptions.map((option) => <option key={option} value={option}>{option === 'active' ? 'Aktif' : 'Tidak aktif'}</option>)}</select>
                    </Field>
                </div>

                <div className="flex justify-end gap-3 border-t border-line bg-slate-50/70 px-5 py-4 sm:px-6">
                    <Link href={athlete ? `/athletes/${athlete.id}` : '/athletes'} className="button-secondary">Batal</Link>
                    <button type="submit" disabled={form.processing} className="button-primary"><Save aria-hidden="true" className="size-4" /> {form.processing ? 'Menyimpan…' : 'Simpan atlet'}</button>
                </div>
            </form>
        </AppLayout>
    );
}

function Field({ label, error, required, children }: { label: string; error?: string; required?: boolean; children: React.ReactElement }) {
    return (
        <label className="block">
            <span className="field-label">{label}{required && <span aria-hidden="true" className="text-risk"> *</span>}</span>
            {children}
            {error && <span className="field-error block">{error}</span>}
        </label>
    );
}
