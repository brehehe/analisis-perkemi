import { CircleCheck, CircleDashed, CircleX, Clock3, TriangleAlert } from 'lucide-react';

const statusStyles: Record<string, string> = {
    active: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    completed: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    win: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    queued: 'border-amber-200 bg-amber-50 text-amber-900',
    uploaded: 'border-blue-200 bg-blue-50 text-blue-800',
    scheduled: 'border-blue-200 bg-blue-50 text-blue-800',
    ready: 'border-blue-200 bg-blue-50 text-blue-800',
    in_analysis: 'border-amber-200 bg-amber-50 text-amber-900',
    pending: 'border-slate-200 bg-slate-50 text-slate-700',
    failed: 'border-red-200 bg-red-50 text-red-800',
    loss: 'border-red-200 bg-red-50 text-red-800',
    inactive: 'border-slate-200 bg-slate-50 text-slate-700',
    cancelled: 'border-slate-200 bg-slate-50 text-slate-700',
};

function StatusIcon({ status }: { status: string }) {
    if (['active', 'completed', 'win'].includes(status)) return <CircleCheck aria-hidden="true" className="size-3.5" />;
    if (['failed', 'loss'].includes(status)) return <CircleX aria-hidden="true" className="size-3.5" />;
    if (['queued', 'in_analysis'].includes(status)) return <Clock3 aria-hidden="true" className="size-3.5" />;
    if (status === 'cancelled') return <TriangleAlert aria-hidden="true" className="size-3.5" />;

    return <CircleDashed aria-hidden="true" className="size-3.5" />;
}

export default function StatusBadge({ status, label }: { status: string; label?: string }) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold ${statusStyles[status] ?? statusStyles.pending}`}
        >
            <StatusIcon status={status} />
            {label ?? status.replaceAll('_', ' ')}
        </span>
    );
}
