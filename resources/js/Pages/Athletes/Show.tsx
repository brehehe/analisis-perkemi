import { Head, Link, router } from '@inertiajs/react';
import { CalendarDays, Dumbbell, Pencil, Swords, Trash2, UserRound } from 'lucide-react';
import EmptyState from '../../Components/EmptyState';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { formatDate, matchTeamName, titleCase } from '../../lib/format';
import type { Athlete } from '../../types';

export default function AthleteShow({ athlete, can }: { athlete: Athlete; can: { update: boolean; delete: boolean } }) {
    const archive = () => {
        if (window.confirm(`Arsipkan ${athlete.name}? Data histori tetap tersimpan.`)) router.delete(`/athletes/${athlete.id}`);
    };

    return (
        <AppLayout title={athlete.name} description={`${athlete.identifier} · ${athlete.category}`} actions={<>{can.update && <Link href={`/athletes/${athlete.id}/edit`} className="button-secondary"><Pencil aria-hidden="true" className="size-4" /> Ubah</Link>}{can.delete && <button type="button" onClick={archive} className="button-secondary text-risk"><Trash2 aria-hidden="true" className="size-4" /> Arsipkan</button>}<Link href="/matches/create" className="button-primary"><Swords aria-hidden="true" className="size-4" /> Buat pertandingan</Link></>}>
            <Head title={athlete.name} />

            <div className="grid gap-5 xl:grid-cols-[minmax(280px,0.75fr)_minmax(0,1.55fr)]">
                <section className="panel rounded-xl p-5">
                    <div className="flex items-center gap-4 border-b border-line pb-5">
                        <div className="grid size-14 place-items-center rounded-xl bg-navy text-white"><UserRound aria-hidden="true" className="size-7" /></div>
                        <div><p className="font-semibold">{athlete.name}</p><div className="mt-1"><StatusBadge status={athlete.status} label={athlete.status === 'active' ? 'Aktif' : 'Tidak aktif'} /></div></div>
                    </div>
                    <dl className="mt-5 grid gap-4 text-sm">
                        <Data label="Klub" value={athlete.club?.name ?? 'Belum ditentukan'} />
                        <Data label="Pelatih" value={athlete.coach?.name ?? 'Belum ditentukan'} />
                        <Data label="Jenis kelamin" value={athlete.gender === 'male' ? 'Putra' : 'Putri'} />
                        <Data label="Tanggal lahir" value={formatDate(athlete.date_of_birth)} />
                        <Data label="Kelas berat" value={athlete.weight_class ? `${athlete.weight_class} kg` : 'Belum diisi'} />
                        <Data label="Pengalaman" value={`${athlete.experience_years ?? 0} tahun`} />
                    </dl>
                </section>

                <div className="grid gap-5">
                    <section className="grid gap-3 sm:grid-cols-3">
                        <Stat icon={Swords} label="Pertandingan" value={athlete.matches_count ?? 0} />
                        <Stat icon={Dumbbell} label="Analisis" value={athlete.analyses_count ?? 0} />
                        <Stat icon={CalendarDays} label="Pengalaman" value={`${athlete.experience_years ?? 0} th`} />
                    </section>

                    <section className="panel overflow-hidden rounded-xl">
                        <div className="border-b border-line px-5 py-4"><h2 className="font-semibold">Pertandingan terbaru</h2><p className="mt-1 text-xs text-ink-soft">Riwayat ini menjadi dasar analisis longitudinal.</p></div>
                        {athlete.matches?.length ? (
                            <div className="divide-y divide-line">
                                {athlete.matches.map((match) => (
                                    <Link key={match.id} href={`/matches/${match.id}`} className="flex min-h-18 items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50">
                                        <div><div className="font-semibold text-navy">{matchTeamName(match)}</div><div className="mt-1 text-xs text-ink-soft">{match.competition_event?.name ?? 'Pertandingan mandiri'} · {formatDate(match.match_date)}</div></div>
                                        <StatusBadge status={match.result} label={titleCase(match.result)} />
                                    </Link>
                                ))}
                            </div>
                        ) : <EmptyState icon={Swords} title="Belum ada pertandingan" description="Tambahkan pertandingan untuk mulai membangun histori performa atlet." action={<Link href="/matches/create" className="button-primary">Buat pertandingan</Link>} />}
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}

function Data({ label, value }: { label: string; value: string }) { return <div><dt className="text-xs text-ink-soft">{label}</dt><dd className="mt-1 font-medium text-ink">{value}</dd></div>; }
function Stat({ icon: Icon, label, value }: { icon: typeof Swords; label: string; value: string | number }) { return <div className="panel flex items-center gap-4 rounded-xl p-5"><div className="grid size-10 place-items-center rounded-lg bg-slate-100 text-navy"><Icon aria-hidden="true" className="size-5" /></div><div><div className="text-2xl font-semibold tracking-[-0.03em]">{value}</div><div className="text-xs text-ink-soft">{label}</div></div></div>; }
