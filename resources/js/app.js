// LogStack Global Helpers
// Dipakai oleh Alpine.js di semua halaman via window.logstackHelpers

window.logstackHelpers = {
    appColor(app) {
        const colors = {
            laravel:    'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/30',
            sogo:       'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-900/30',
            nextcloud:  'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30',
            odoo:       'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-900/30',
            onlyoffice: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30',
            grafana:    'bg-yellow-50 dark:bg-yellow-950/40 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-900/30',
        };
        return colors[app] || 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-gray-400 border border-slate-200 dark:border-slate-700';
    },
    actionColor(action) {
        const colors = {
            login:           'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400',
            logout:          'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400',
            open_app:        'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400',
            open_document:   'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400',
            create_document: 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400',
            delete_document: 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400',
        };
        return colors[action] || 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-gray-400';
    },
    actionIcon(action) {
        const icons = {
            login:           'fa-sign-in-alt',
            logout:          'fa-sign-out-alt',
            open_app:        'fa-external-link-alt',
            open_document:   'fa-file-alt',
            create_document: 'fa-file-plus',
            delete_document: 'fa-trash-alt',
        };
        return icons[action] || 'fa-circle';
    },
    formatAction(action) {
        return action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
};