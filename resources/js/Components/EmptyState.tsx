import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

export default function EmptyState({
    icon: Icon,
    title,
    description,
    action,
}: {
    icon: LucideIcon;
    title: string;
    description: string;
    action?: ReactNode;
}) {
    return (
        <div className="flex min-h-64 flex-col items-center justify-center px-6 py-12 text-center">
            <div className="grid size-12 place-items-center rounded-full bg-slate-100 text-navy">
                <Icon aria-hidden="true" className="size-5" />
            </div>
            <h2 className="mt-4 text-lg font-semibold tracking-[-0.01em]">{title}</h2>
            <p className="mt-2 max-w-md text-sm text-ink-soft">{description}</p>
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}
