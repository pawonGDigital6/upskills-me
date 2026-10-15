import domReady from '@wordpress/dom-ready';
import { Announcement } from './components/announcement';
import { Navigation } from './components/navigation';
import { Search } from './components/search';
import { Disclosure } from './components/disclosure';
import { Reveal } from './components/reveal';
import { DeferredVideo } from './components/deferred-video';
import { Article } from './components/article';
import { PasswordToggle } from './components/password-toggle';
import { Library } from './components/library';
import { BuyBox } from './components/buy-box';

domReady( () => {
	Announcement();
	Navigation();
	Search();
	Disclosure();
	Reveal();
	DeferredVideo();
	Article();
	PasswordToggle();
	Library();
	BuyBox();
} );
