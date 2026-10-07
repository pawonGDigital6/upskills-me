import domReady from '@wordpress/dom-ready';
import { Announcement } from './components/announcement';
import { Navigation } from './components/navigation';
import { Search } from './components/search';
import { Disclosure } from './components/disclosure';
import { Reveal } from './components/reveal';
import { DeferredVideo } from './components/deferred-video';
import { Article } from './components/article';

domReady( () => {
	Announcement();
	Navigation();
	Search();
	Disclosure();
	Reveal();
	DeferredVideo();
	Article();
} );
