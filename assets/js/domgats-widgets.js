( function ( $, window, document ) {
	'use strict';

	const widgets = new Map();

	/**
	 * Safe JSON parse.
	 */
	function parseJSON( value, fallback = {} ) {
		try {
			return JSON.parse( value );
		} catch ( e ) {
			return fallback;
		}
	}

	/**
	 * Read filters from query string for deep linking.
	 */
	function getFiltersFromQuery() {
		const params = new URLSearchParams( window.location.search );
		const terms = params.getAll( 'dg_term' );
		return {
			terms,
			meta: params.getAll( 'dg_meta' ),
		};
	}

	/**
	 * Update query string for deep linking.
	 */
	function updateQueryString( filters ) {
		const params = new URLSearchParams( window.location.search );
		params.delete( 'dg_term' );
		params.delete( 'dg_meta' );
		if ( Array.isArray( filters.terms ) ) {
			filters.terms.forEach( ( term ) => {
				if ( term ) {
					params.append( 'dg_term', term );
				}
			} );
		}
		if ( Array.isArray( filters.meta ) ) {
			filters.meta.forEach( ( val ) => {
				if ( val ) {
					params.append( 'dg_meta', val );
				}
			} );
		}
		const newUrl = `${ window.location.pathname }?${ params.toString() }${ window.location.hash }`;
		window.history.replaceState( {}, '', newUrl );
	}

	/**
	 * Dispatch analytics hook.
	 */
	function dispatchHook( name, detail ) {
		document.dispatchEvent( new CustomEvent( name, { detail } ) );
	}

	/**
	 * Initialize animations via IntersectionObserver.
	 */
	function initAnimations( $widget ) {
		const cards = $widget.find( '.domgats-animate' );
		if ( ! cards.length || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		const observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{
				threshold: 0.15,
			}
		);

		cards.each( function () {
			const delay = this.getAttribute( 'data-animate-delay' );
			if ( delay ) {
				this.style.setProperty( '--domgats-delay', `${ delay }ms` );
			}
			observer.observe( this );
		} );
	}

	/**
	 * Initialize slider if needed.
	 */
	function initSlider( $widget ) {
		const layout = $widget.find( '.domgats-grid' ).data( 'layout' );
		if ( layout !== 'slider' ) {
			return;
		}

		if ( window.elementorFrontend && elementorFrontend.utils && elementorFrontend.utils.swiper ) {
			const $slider = $widget.find( '.domgats-grid' );
			if ( $slider.data( 'swiper-instance' ) ) {
				return;
			}

			const swiper = new elementorFrontend.utils.swiper( $slider, {
				slidesPerView: 'auto',
				spaceBetween: 24,
				freeMode: true,
			} );

			$slider.data( 'swiper-instance', swiper );
		}
	}

	/**
	 * Render loading placeholder.
	 */
	function showLoading( $widget ) {
		const loading = $widget.find( '.domgats-loading' );
		if ( loading.length ) {
			loading.addClass( 'is-active' );
		}
	}

	function hideLoading( $widget ) {
		$widget.find( '.domgats-loading' ).removeClass( 'is-active' );
	}

	/**
	 * Apply filters to state.
	 */
	function setFilters( widgetId, filters ) {
		const state = widgets.get( widgetId );
		state.filters = filters;
	}

	/**
	 * Get filters from UI.
	 */
	function readFiltersFromUI( $widget, config ) {
		const filters = { ...config.filters };
		const $select = $widget.find( '[data-filter-control="terms"]' );
		const $buttons = $widget.find( '[data-filter-click="terms"].is-active' );

		if ( $select.length ) {
			const val = $select.val();
			filters.terms = val && val.length ? ( Array.isArray( val ) ? val : [ val ] ) : [];
		} else {
			const terms = [];
			$buttons.each( function () {
				const term = this.getAttribute( 'data-term' );
				if ( term || term === '' ) {
					terms.push( term );
				}
			} );
			filters.terms = terms;
		}

		const $metaSelect = $widget.find( '[data-filter-control="meta"]' );
		const $metaButtons = $widget.find( '[data-filter-click="meta"].is-active' );

		if ( $metaSelect.length ) {
			const val = $metaSelect.val();
			filters.meta = val && val.length ? ( Array.isArray( val ) ? val : [ val ] ) : [];
		} else if ( $metaButtons.length ) {
			const metaVals = [];
			$metaButtons.each( function () {
				metaVals.push( this.getAttribute( 'data-term' ) );
			} );
			filters.meta = metaVals;
		} else {
			filters.meta = [];
		}

		return filters;
	}

	/**
	 * Fetch data from REST endpoint.
	 */
	function fetchData( widgetId, page = 1, append = false ) {
		const ctx = widgets.get( widgetId );
		if ( ! ctx || ctx.loading ) {
			return;
		}

		const { $el, config } = ctx;
		ctx.loading = true;
		showLoading( $el );

		const body = {
			settings: config.settings,
			filters: ctx.filters,
			page,
		};

		return fetch( config.restUrl || ( window.domgatsWidgetsData && window.domgatsWidgetsData.restUrl ), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': config.nonce || ( window.domgatsWidgetsData && window.domgatsWidgetsData.nonce ) || '',
			},
			body: JSON.stringify( body ),
		} )
			.then( ( res ) => res.json() )
			.then( ( data ) => {
				const $grid = $el.find( '.domgats-grid' );
				const $pagination = $el.find( '.domgats-pagination' );

				if ( append ) {
					$grid.append( data.html );
				} else {
					$grid.html( data.html );
				}
				$pagination.replaceWith( data.pagination );

				ctx.page = data.page;
				ctx.maxPages = data.max_pages;

				initAnimations( $el );
				initSlider( $el );
				attachInfiniteObserver( widgetId );
				hideLoading( $el );
				ctx.loading = false;

				dispatchHook( 'domgats:grid:update', { widgetId, page: data.page } );
			} )
			.catch( () => {
				hideLoading( $el );
				ctx.loading = false;
			} );
	}

	/**
	 * Infinite scroll observer.
	 */
	function attachInfiniteObserver( widgetId ) {
		const ctx = widgets.get( widgetId );
		if ( ! ctx ) return;

		const { $el, config } = ctx;
		if ( config.paginationType !== 'infinite' ) {
			return;
		}

		const sentinel = $el.find( '.domgats-infinite-sentinel' ).get( 0 );
		if ( ! sentinel || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		const observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						const next = sentinel.getAttribute( 'data-next' );
						if ( next ) {
							fetchData( widgetId, parseInt( next, 10 ), true );
						}
						observer.unobserve( sentinel );
					}
				} );
			},
			{ rootMargin: '200px' }
		);

		observer.observe( sentinel );
	}

	/**
	 * Bind UI events.
	 */
	function bindEvents( $widget, widgetId ) {
		$widget.on( 'change', '[data-filter-control]', function () {
			const ctx = widgets.get( widgetId );
			ctx.filters = readFiltersFromUI( $widget, ctx.config );
			if ( ctx.config.deepLink ) {
				updateQueryString( ctx.filters );
			}
			dispatchHook( 'domgats:filter-change', { widgetId, filters: ctx.filters } );
			fetchData( widgetId, 1, false );
		} );

		$widget.on( 'click', '[data-filter-click]', function () {
			const multi = this.getAttribute( 'data-multi' );
			const term = this.getAttribute( 'data-term' );
			const group = this.getAttribute( 'data-filter-click' );
			const $container = $( this ).closest( '.domgats-filter' );
			if ( multi ) {
				this.classList.toggle( 'is-active' );
			} else {
				$container.find( `[data-filter-click="${ group }"]` ).removeClass( 'is-active' );
				if ( term === '' ) {
					$( this ).addClass( 'is-active' );
				} else {
					this.classList.add( 'is-active' );
				}
			}
			const ctx = widgets.get( widgetId );
			ctx.filters = readFiltersFromUI( $widget, ctx.config );
			if ( ctx.config.deepLink ) {
				updateQueryString( ctx.filters );
			}
			dispatchHook( 'domgats:filter-change', { widgetId, filters: ctx.filters } );
			fetchData( widgetId, 1, false );
		} );

		$widget.on( 'change', '[data-sort]', function () {
			const val = this.value;
			const [ orderby, order ] = val.split( '|' );
			const ctx = widgets.get( widgetId );
			ctx.config.settings.query_orderby = orderby;
			ctx.config.settings.query_order = order;
			dispatchHook( 'domgats:sort-change', { widgetId, orderby, order } );
			fetchData( widgetId, 1, false );
		} );

		$widget.on( 'click', '.domgats-page', function () {
			const page = parseInt( this.getAttribute( 'data-page' ), 10 );
			fetchData( widgetId, page, false );
		} );

		$widget.on( 'click', '.domgats-load-more', function () {
			const next = parseInt( this.getAttribute( 'data-next' ), 10 );
			fetchData( widgetId, next, true );
			this.remove();
		} );

		$widget.on( 'click', '.domgats-card a', function () {
			dispatchHook( 'domgats:item-click', { widgetId, href: this.href } );
		} );
	}

	/**
	 * Setup widget instance.
	 */
	function initWidget( node ) {
		const $widget = $( node );
		const config = parseJSON( $widget.attr( 'data-config' ), {} );
		if ( ! config.widgetId ) {
			return;
		}

		// Deep link override.
		if ( config.deepLink ) {
			const deepFilters = getFiltersFromQuery();
			if ( deepFilters.terms.length || deepFilters.meta.length ) {
				config.filters = deepFilters;
			}
		}

		const startingFilters = config.filters || {};
		if ( ! startingFilters.terms ) {
			startingFilters.terms = [];
		}
		if ( ! startingFilters.meta ) {
			startingFilters.meta = [];
		}

		widgets.set( config.widgetId, {
			$el: $widget,
			config,
			filters: startingFilters,
			page: 1,
			maxPages: 1,
			loading: false,
		} );

		bindEvents( $widget, config.widgetId );
		initAnimations( $widget );
		initSlider( $widget );
		attachInfiniteObserver( config.widgetId );
	}

	$( window ).on( 'elementor/frontend/init', () => {
		const widgetSelector = '[data-domgats-grid]';

		const init = () => {
			$( widgetSelector ).each( function () {
				const widgetId = this.getAttribute( 'data-widget-id' );
				if ( ! widgets.has( widgetId ) ) {
					initWidget( this );
				}
			} );
		};

		init();

		// In editor mode, re-init on preview changes.
		$( window ).on( 'elementor/popup/show elementor/frontend/init', init );
	} );
}( jQuery, window, document ) );
