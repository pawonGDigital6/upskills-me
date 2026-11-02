import domReady from '@wordpress/dom-ready';
import { Announcement } from './components/announcement';
import { Navigation } from './components/navigation';
import { Search } from './components/search';
import { Disclosure } from './components/disclosure';
import { Reveal } from './components/reveal';
import { DeferredVideo } from './components/deferred-video';
import { VideoPlayer } from './components/video-player';
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
	VideoPlayer();
	Article();
	PasswordToggle();
	Library();
	BuyBox();
	// The course hero gallery (Swiper) loads only on a course page.
	if ( document.querySelector( '[data-course-gallery]' ) ) {
		import( /* webpackChunkName: "course-gallery" */ './components/course-gallery' ).then( ( { CourseGallery } ) => CourseGallery() );
	}
} );
