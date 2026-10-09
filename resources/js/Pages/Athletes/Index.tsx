import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, UserRoundSearch, UsersRound } from 'lucide-react';
import { useState } from 'react';
import EmptyState from '../../Components/EmptyState';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import type { Athlete, Paginated } from '../../types';

type Props = {
    athletes: Paginated<Athlete>;
    filters: { search: string; status: string; category: string };
    categories: string[];
    can: { create: boolean };
};

export default function AthleteIndex({ athletes, filters, categories, can }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [category, setCategory] = useState(filters.category);

    const applyFilters = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get('/athletes', { search, status, category }, { preserveState: true, replace: true });
    };

    return (
        <AppLayout
            title="Atlet"
            description="Kelola profil, pendampingan pelatih, kategori, dan histori pertandingan setiap atlet."
            actions={can.create ? <Link href="/athletes/create" className="button-primary"><Plus aria-hidden="true" className="size-4" /> Tambah atlet</Link> : undefined}
        >
            <Head title="Atlet" />

            <section className="panel overflow-hidden rounded-xl">
                <form onSubmit={applyFilters} className="grid gap-3 border-b border-line bg-slate-50/70 p-4 md:grid-cols-[minmax(220px,1fr)_200px_220px_auto]">
                    <div className="relative">
                        <label htmlFor="athlete-search" className="sr-only">Cari atlet</label>
                        <Search aria-hidden="true" className="pointer-events-none absolute left-3 top-3.5 size-4 text-slate-500" />
                        <input id="athlete-search" className="field-control pl-10" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Cari nama atau NIK" />
                    </div>
                    <div>
                        <label htmlFor="athlete-status" className="sr-only">Status atlet</label>
                        <select id="athlete-status" className="field-control" value={status} onChange={(event) => setStatus(event.target.value)}>
                            <option value="">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                    </div>
                    <div>
                        <label htmlFor="athlete-category" className="sr-only">Kategori atlet</label>
                        <select id="athlete-category" className="field-control" value={category} onChange={(event) => setCategory(event.target.value)}>
                            <option value="">Semua kategori</option>
                            {categories.map((option) => <option key={option} value={option}>{option}</option>)}
                        </select>
                    </div>
                    <button type="submit" className="button-secondary"><Search aria-hidden="true" className="size-4" /> Terapkan</button>
                </form>

                {athletes.data.length ? (
                    <>
                        <div className="hidden overflow-x-auto md:block">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="border-b border-line bg-white text-xs text-ink-soft">
                                    <tr>
                                        <th className="px-5 py-3 font-semibold">Atlet</th>
                                        <th className="px-5 py-3 font-semibold">Kategori</th>
                                        <th className="px-5 py-3 font-semibold">Klub / pelatih</th>
                                        <th className="px-5 py-3 font-semibold">Pertandingan</th>
                                        <th className="px-5 py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-line">
                                    {athletes.data.map((athlete) => (
                                        <tr key={athlete.id} className="hover:bg-slate-50/70">
                                            <td className="px-5 py-4">
                                                <Link href={`/athletes/${athlete.id}`} className="font-semibold text-navy hover:underline">{athlete.name}</Link>
                                                <div className="mt-1 text-xs text-ink-soft">{athlete.identifier} · {athlete.gender === 'male' ? 'Putra' : 'Putri'}</div>
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="font-medium">{athlete.category}</div>
                                                <div className="mt-1 text-xs text-ink-soft">{athlete.weight_class ? `${athlete.weight_class} kg` : 'Kelas berat belum diisi'}</div>
                                            </td>
                                            <td className="px-5 py-4">
                                                <div>{athlete.club?.name ?? 'Tanpa klub'}</div>
                                                <div className="mt-1 text-xs text-ink-soft">{athlete.coach?.name ?? 'Belum ada pelatih'}</div>
                                            </td>
                                            <td className="px-5 py-4 font-semibold">{athlete.matches_count ?? 0}</td>
                                            <td className="px-5 py-4"><StatusBadge status={athlete.status} label={athlete.status === 'active' ? 'Aktif' : 'Tidak aktif'} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="divide-y divide-line md:hidden">
                            {athletes.data.map((athlete) => (
                                <Link key={athlete.id} href={`/athletes/${athlete.id}`} className="block p-4 hover:bg-slate-50">
                                    <div className="flex items-start justify-between gap-3">
                                        <div><div className="font-semibold text-navy">{athlete.name}</div><div className="mt-1 text-xs text-ink-soft">{athlete.identifier}</div></div>
                                        <StatusBadge status={athlete.status} label={athlete.status === 'active' ? 'Aktif' : 'Tidak aktif'} />
                                    </div>
                                    <div className="mt-4 grid grid-cols-2 gap-3 text-sm"><div><span className="text-xs text-ink-soft">Kategori</span><div className="mt-1 font-medium">{athlete.category}</div></div><div><span className="text-xs text-ink-soft">Pertandingan</span><div className="mt-1 font-medium">{athlete.matches_count ?? 0}</div></div></div>
                                </Link>
                            ))}
                        </div>
                        <Pagination paginator={athletes} />
                    </>
                ) : (
                    <EmptyState icon={filters.search || filters.status || filters.category ? UserRoundSearch : UsersRound} title="Tidak ada atlet ditemukan" description={filters.search || filters.status || filters.category ? 'Ubah kata kunci atau filter untuk melihat hasil lain.' : 'Tambahkan atlet pertama untuk mulai mencatat pertandingan dan performa.'} action={can.create && !filters.search ? <Link href="/athletes/create" className="button-primary">Tambah atlet</Link> : undefined} />
                )}
            </section>
        </AppLayout>
    );
}
