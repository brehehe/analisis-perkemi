type AppLogoProps = {
    compact?: boolean;
    tone?: 'dark' | 'light';
};

export default function AppLogo({ compact = false, tone = 'dark' }: AppLogoProps) {
    return (
        <div className="flex items-center gap-3">
            <svg
                aria-hidden="true"
                className="size-10 shrink-0"
                viewBox="0 0 48 48"
                fill="none"
            >
                <path d="M24 3 43 14v20L24 45 5 34V14L24 3Z" fill="#173a5e" />
                <path
                    d="M16 16.5h16M16 24h16M19.5 31.5h9"
                    stroke="white"
                    strokeWidth="3"
                    strokeLinecap="round"
                />
                <path d="M24 12v24" stroke="#d49a45" strokeWidth="3" strokeLinecap="round" />
            </svg>
            {!compact && (
                <div className="leading-tight">
                    <div className={`font-semibold tracking-[-0.02em] ${tone === 'dark' ? 'text-white' : 'text-ink'}`}>Smart-PERKEMI</div>
                    <div className={`mt-1 text-xs ${tone === 'dark' ? 'text-slate-300' : 'text-ink-soft'}`}>Performance intelligence</div>
                </div>
            )}
        </div>
    );
}
