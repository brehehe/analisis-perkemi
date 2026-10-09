import { Link, router, usePage } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import {
    BarChart3,
    ChevronRight,
    LayoutDashboard,
    LogOut,
    Menu,
    Swords,
    UsersRound,
    Video,
    X,
} from 'lucide-react';
import { useState, type ReactNode } from 'react';
import AppLogo from '../Components/AppLogo';
import type { SharedPageProps } from '../types';

const navigation = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, permission: 'dashboard.view' },
    { label: 'Atlet', href: '/athletes', icon: UsersRound, permission: 'athletes.view' },
    { label: 'Pertandingan', href: '/matches', icon: Swords, permission: 'matches.view' },
    { label: 'Antrean analisis', href: '/analyses', icon: Video, permission: 'analysis.view' },
];

function isActive(url: string, href: string): boolean {
    return href === '/dashboard' ? url.startsWith('/dashboard') : url.startsWith(href);
}

export default function AppLayout({
    children,
    title,
    description,
    actions,
}: {
    children: ReactNode;
    title: string;
    description?: string;
    actions?: ReactNode;
}) {
    const { auth, flash } = usePage<SharedPageProps>().props;
    const url = usePage().url;
    const prefersReducedMotion = useReducedMotion();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const allowedNavigation = navigation.filter((item) => auth.user?.permissions.includes(item.permission));

    const logout = () => router.post('/logout');

    return (
        <div className="min-h-screen bg-paper">
            <a
                href="#main-content"
                className="fixed left-4 top-3 z-50 -translate-y-20 rounded-md bg-white px-4 py-2 font-semibold text-navy shadow-lg focus:translate-y-0"
            >
                Lewati navigasi
            </a>

            <aside className="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-navy-deep lg:flex">
                <div className="border-b border-white/10 px-6 py-6"><AppLogo /></div>
                <nav aria-label="Navigasi utama" className="flex-1 px-3 py-5">
                    <div className="flex flex-col gap-1">
                        {allowedNavigation.map((item) => {
                            const Icon = item.icon;
                            const active = isActive(url, item.href);
                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    aria-current={active ? 'page' : undefined}
                                    className={`flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors ${active ? 'bg-white text-navy-deep' : 'text-slate-200 hover:bg-white/8 hover:text-white'}`}
                                >
                                    <Icon aria-hidden="true" className="size-5" />
                                    {item.label}
                                    {active && <ChevronRight aria-hidden="true" className="ml-auto size-4" />}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
                <div className="border-t border-white/10 p-4">
                    <div className="px-2 pb-3">
                        <p className="truncate text-sm font-semibold text-white">{auth.user?.name}</p>
                        <p className="mt-1 truncate text-xs text-slate-300">{auth.user?.roles.join(', ')}</p>
                    </div>
                    <button onClick={logout} className="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-medium text-slate-200 hover:bg-white/8 hover:text-white">
                        <LogOut aria-hidden="true" className="size-5" /> Keluar
                    </button>
                </div>
            </aside>

            <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur lg:hidden">
                <div className="flex items-center gap-3">
                    <AppLogo compact />
                    <span className="text-sm font-semibold text-navy-deep">Smart-PERKEMI</span>
                </div>
                <button
                    type="button"
                    aria-label={mobileMenuOpen ? 'Tutup menu' : 'Buka menu'}
                    aria-expanded={mobileMenuOpen}
                    onClick={() => setMobileMenuOpen((open) => !open)}
                    className="grid size-11 place-items-center rounded-lg text-navy hover:bg-slate-100"
                >
                    {mobileMenuOpen ? <X aria-hidden="true" className="size-5" /> : <Menu aria-hidden="true" className="size-5" />}
                </button>
            </header>

            {mobileMenuOpen && (
                <div className="fixed inset-x-3 top-19 z-40 rounded-xl border border-line bg-white p-2 shadow-xl lg:hidden">
                    {allowedNavigation.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => setMobileMenuOpen(false)}
                                className="flex min-h-12 items-center gap-3 rounded-lg px-3 text-sm font-medium text-ink hover:bg-slate-100"
                            >
                                <Icon aria-hidden="true" className="size-5 text-navy" /> {item.label}
                            </Link>
                        );
                    })}
                    <button onClick={logout} className="flex min-h-12 w-full items-center gap-3 rounded-lg px-3 text-sm font-medium text-risk hover:bg-red-50">
                        <LogOut aria-hidden="true" className="size-5" /> Keluar
                    </button>
                </div>
            )}

            <div className="lg:pl-64">
                <motion.main
                    id="main-content"
                    initial={prefersReducedMotion ? false : { opacity: 0, y: 6 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.18 }}
                    className="mx-auto min-h-screen max-w-[1480px] px-4 pb-24 pt-7 sm:px-6 lg:px-8 lg:pb-10 lg:pt-9"
                >
                    <div className="mb-7 flex flex-col gap-4 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-[-0.025em] text-ink sm:text-[1.75rem]">{title}</h1>
                            {description && <p className="mt-2 max-w-3xl text-sm leading-6 text-ink-soft">{description}</p>}
                        </div>
                        {actions && <div className="flex shrink-0 flex-wrap gap-2">{actions}</div>}
                    </div>

                    {flash.success && (
                        <div role="status" className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div role="alert" className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                            {flash.error}
                        </div>
                    )}

                    {children}
                </motion.main>
            </div>

            <nav aria-label="Navigasi seluler" className="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-line bg-white px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-8px_24px_rgb(20_40_60_/_0.08)] lg:hidden">
                {allowedNavigation.slice(0, 4).map((item) => {
                    const Icon = item.icon;
                    const active = isActive(url, item.href);
                    return (
                        <Link key={item.href} href={item.href} aria-current={active ? 'page' : undefined} className={`flex min-h-13 flex-col items-center justify-center gap-1 rounded-lg text-[0.6875rem] font-semibold ${active ? 'text-navy' : 'text-slate-500'}`}>
                            <Icon aria-hidden="true" className="size-5" />
                            {item.label === 'Antrean analisis' ? 'Analisis' : item.label}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
