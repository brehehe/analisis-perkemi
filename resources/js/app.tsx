import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

const applicationName = import.meta.env.VITE_APP_NAME ?? 'Smart-PERKEMI';
const pages = import.meta.glob<{ default: ComponentType }>('./Pages/**/*.tsx');

void createInertiaApp({
    title: (title) => (title ? `${title} — ${applicationName}` : applicationName),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.tsx`];

        if (!page) throw new Error(`Unknown Inertia page: ${name}`);

        return page().then((module) => module.default);
    },
    setup({ el, App, props }) {
        createRoot(el as HTMLElement).render(<App {...props} />);
    },
    progress: {
        color: '#1e3a5f',
        showSpinner: false,
    },
    strictMode: true,
});
