import './js/main';

// Tailwind first, then the SCSS: the SCSS only styles chrome Tailwind cannot
// reach (state-driven header/menu/drawer, plugin markup) or deliberately
// overrides a utility, and source order is clearer than !important.
import './tailwind/tailwind.css';
import './scss/main.scss';
