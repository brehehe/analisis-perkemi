import { Head, useForm } from '@inertiajs/react';
import { Activity, ArrowRight, CheckCircle2, ShieldCheck } from 'lucide-react';
import AppLogo from '../../Components/AppLogo';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

export default function Login() {
    const form = useForm<LoginForm>({ email: '', password: '', remember: false });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post('/login', { onFinish: () => form.reset('password') });
    };

    return (
        <>
            <Head title="Masuk" />
            <main className="grid min-h-screen bg-paper lg:grid-cols-[minmax(0,1.05fr)_minmax(440px,0.95fr)]">
                <section className="relative hidden overflow-hidden bg-navy-deep px-12 py-10 text-white lg:flex lg:flex-col">
                    <div className="absolute inset-0 opacity-30 [background-image:linear-gradient(rgb(255_255_255_/_0.08)_1px,transparent_1px),linear-gradient(90deg,rgb(255_255_255_/_0.08)_1px,transparent_1px)] [background-size:48px_48px]" />
                    <div className="relative"><AppLogo /></div>

                    <div className="relative my-auto max-w-2xl py-16">
                        <div className="mb-7 flex items-center gap-3 text-sm font-medium text-slate-200">
                            <span className="h-px w-12 bg-signal" /> Ruang keputusan pelatih
                        </div>
                        <h1 className="max-w-xl text-5xl font-semibold leading-[1.06] tracking-[-0.045em]">
                            Dari rekaman pertandingan menjadi keputusan latihan.
                        </h1>
                        <p className="mt-6 max-w-xl text-lg leading-8 text-slate-300">
                            Smart-PERKEMI menyatukan video, pengukuran performa, validasi analis, dan catatan pelatih dalam satu alur kerja.
                        </p>

                        <div className="mt-12 grid max-w-xl grid-cols-3 border-y border-white/15 py-6">
                            {[
                                ['01', 'Ukur performa'],
                                ['02', 'Temukan peluang'],
                                ['03', 'Validasi keputusan'],
                            ].map(([number, label]) => (
                                <div key={number} className="border-l border-white/15 px-5 first:border-l-0 first:pl-0">
                                    <div className="text-2xl font-semibold text-signal">{number}</div>
                                    <div className="mt-2 text-sm text-slate-200">{label}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="relative flex items-center gap-2 text-sm text-slate-300">
                        <ShieldCheck aria-hidden="true" className="size-4" /> AI membantu; pelatih tetap memutuskan.
                    </div>
                </section>

                <section className="flex items-center justify-center px-5 py-10 sm:px-10">
                    <div className="w-full max-w-md">
                        <div className="mb-10 lg:hidden"><AppLogo tone="light" /></div>
                        <div className="mb-8">
                            <div className="mb-5 grid size-12 place-items-center rounded-xl bg-slate-200 text-navy">
                                <Activity aria-hidden="true" className="size-6" />
                            </div>
                            <h2 className="text-3xl font-semibold tracking-[-0.035em] text-ink">Masuk ke ruang analisis</h2>
                            <p className="mt-3 text-sm leading-6 text-ink-soft">Gunakan akun yang diberikan administrator PERKEMI.</p>
                        </div>

                        <form onSubmit={submit} className="space-y-5" noValidate>
                            <div>
                                <label className="field-label" htmlFor="email">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    autoComplete="username"
                                    autoFocus
                                    className="field-control"
                                    value={form.data.email}
                                    onChange={(event) => form.setData('email', event.target.value)}
                                    aria-invalid={Boolean(form.errors.email)}
                                    aria-describedby={form.errors.email ? 'email-error' : undefined}
                                />
                                {form.errors.email && <p id="email-error" className="field-error">{form.errors.email}</p>}
                            </div>

                            <div>
                                <label className="field-label" htmlFor="password">Kata sandi</label>
                                <input
                                    id="password"
                                    type="password"
                                    autoComplete="current-password"
                                    className="field-control"
                                    value={form.data.password}
                                    onChange={(event) => form.setData('password', event.target.value)}
                                    aria-invalid={Boolean(form.errors.password)}
                                />
                                {form.errors.password && <p className="field-error">{form.errors.password}</p>}
                            </div>

                            <label className="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-ink-soft">
                                <input
                                    type="checkbox"
                                    checked={form.data.remember}
                                    onChange={(event) => form.setData('remember', event.target.checked)}
                                    className="size-4 rounded border-slate-400 text-navy focus:ring-navy"
                                />
                                Pertahankan sesi di perangkat ini
                            </label>

                            <button type="submit" disabled={form.processing} className="button-primary w-full">
                                {form.processing ? 'Memeriksa akun…' : 'Masuk'}
                                {!form.processing && <ArrowRight aria-hidden="true" className="size-4" />}
                            </button>
                        </form>

                        <div className="mt-8 flex items-start gap-3 border-t border-line pt-6 text-sm leading-6 text-ink-soft">
                            <CheckCircle2 aria-hidden="true" className="mt-1 size-4 shrink-0 text-positive" />
                            Sistem mencatat tindakan penting untuk menjaga keterlacakan analisis dan laporan.
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
