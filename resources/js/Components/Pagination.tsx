import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { Paginated } from '../types';

export default function Pagination({ paginator }: { paginator: Pick<Paginated<unknown>, 'current_page' | 'last_page' | 'from' | 'to' | 'total' | 'links'> }) {
    if (paginator.last_page <= 1) return null;

    const previous = paginator.links[0];
    const next = paginator.links[paginator.links.length - 1];

    return (
        <nav aria-label="Paginasi" className="flex flex-col gap-3 border-t border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p className="text-sm text-ink-soft">
                Menampilkan {paginator.from}–{paginator.to} dari {paginator.total}
            </p>
            <div className="flex items-center gap-2">
                {previous.url ? (
                    <Link className="button-secondary min-h-10 px-3" href={previous.url} preserveScroll>
                        <ChevronLeft aria-hidden="true" className="size-4" /> Sebelumnya
                    </Link>
                ) : (
                    <span aria-disabled="true" className="button-secondary min-h-10 px-3 opacity-45">
                        <ChevronLeft aria-hidden="true" className="size-4" /> Sebelumnya
                    </span>
                )}
                <span className="px-2 text-sm font-medium">{paginator.current_page} / {paginator.last_page}</span>
                {next.url ? (
                    <Link className="button-secondary min-h-10 px-3" href={next.url} preserveScroll>
                        Berikutnya <ChevronRight aria-hidden="true" className="size-4" />
                    </Link>
                ) : (
                    <span aria-disabled="true" className="button-secondary min-h-10 px-3 opacity-45">
                        Berikutnya <ChevronRight aria-hidden="true" className="size-4" />
                    </span>
                )}
            </div>
        </nav>
    );
}
