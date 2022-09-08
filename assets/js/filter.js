import { debounce } from "lodash";
import { Flipper, spring } from 'flip-toolkit';

/**
 * Class for the filter posts bundle
 * 
 * @property {HTMLElement} pagination - the element with the button(s) for paginatinon
 * @property {HTMLElement} content - the element with the main content
 * @property {HTMLElement} sorting - the element with the button(s) for sorting
 * @property {HTMLFormElement} Form - the form for the search
 * @property {HTMLElement} count - the element with the number of posts on the content
 * @property {number} page - the number of the page search
 */
export default class Filter {
    /**
     * Constructor the Filter class
     * 
     * @param {HTMLElement} section - Element parant
     */
    constructor(section) {
        if (section == null) {
            return;
        }

        this.pagination = section.querySelector('.js-filter-pagination');
        this.content = section.querySelector('.js-filter-content');
        this.sorting = section.querySelector('.js-filter-sorting');
        this.form = section.querySelector('.js-filter-form');
        this.count = section.querySelector('.js-filter-count');
        this.page = parseInt(new URLSearchParams(window.location.search).get('page') || 1);
        this.moreNav = this.page == 1;
        this.bindEvents();
    }

    /**
     * Add th action to the elements of the filter bundle
     */
    bindEvents() {
        const linkClickListener = (e) => {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                this.loadUrl(e.target.getAttribute('href'));
            }
        }

        if (this.moreNav) {
            this.pagination.innerHTML = '<button class="btn btn-primary btn-show-more mt-2">Voir plus</button>';
            this.pagination.querySelector('button').addEventListener('click', this.loadMore.bind(this));
        } else {
            this.pagination.addEventListener('click', linkClickListener);
        }

        this.sorting.addEventListener('click', element => {
            linkClickListener(element);
            this.page = 1;
        });

        this.form.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', debounce(this.loadForm.bind(this), 300));
        });

        this.form.querySelector('input[type="text"]')
            .addEventListener('keyup', debounce(this.loadForm.bind(this), 500));
    }

    /**
     * Load the url in ajax
     * @param {URL} URL - Url to load
     */
    async loadUrl(url, append = false) {
        this.showLoader();
        this.content.classList.remove('content-response');
        const params = new URLSearchParams(url.split('?')[1] || '');
        params.set('ajax', 1);

        const response = await fetch(`${url.split('?')[0]}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.status >= 200 && response.status < 300) {
            const data = await response.json();

            this.sorting.innerHTML = data.sorting;
            this.count.innerHTML = data.count;

            this.flipContent(data.content, append);

            this.content.classList.add('content-response');
            if (!this.moreNav) {
                this.pagination.innerHTML = data.pagination;
            } else if (this.page === data.pages) {
                this.pagination.style.display = 'none';
            } else {
                this.pagination.style.display = null;
            }

            params.delete('ajax');
            history.replaceState({}, '', `${url.split('?')[0]}?${params.toString()}`);
        } else {
            console.error(response);
        }

        this.hideLoader();
    }

    /**
     * Get the form and send request ajax with informations
     * 
     */
    async loadForm() {
        this.page = 1;
        const data = new FormData(this.form);
        const url = new URL(this.form.getAttribute('action') || window.location.href);
        const params = new URLSearchParams();

        data.forEach((value, key) => {
            params.append(key, value);
        })

        return this.loadUrl(`${url.pathname}?${params.toString()}`);
    }

    /**
     * Load more content on the page
     * 
     * @param {HTMLElement} button - button show more 
     */
    async loadMore(button) {
        button.target.setAttribute('disabled', true);
        this.page++;
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        params.set('page', this.page);
        await this.loadUrl(`${url.pathname}?${params.toString()}`, true);
        button.target.removeAttribute('disabled');
    }

    flipContent(content, append) {
        const springName = 'veryGentle';
        const exitSpring = function (element, index, onComplete) {
            spring({
                config: 'stiff',
                values: {
                    translateY: [0, -20],
                    opacity: [1, 0]
                },
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    element.style.transform = `translateY(${translateY}px)`;
                },
                onComplete
            })
        }
        const appearSpring = function (element, index) {
            spring({
                config: springName,
                values: {
                    translateY: [0, 20],
                    opacity: [0, 1]
                },
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    element.style.transform = `translateY(${translateY}px)`;
                },
                delay: index * 10
            })
        }

        const flipper = new Flipper({
            element: this.content
        })

        let cards = this.content.children;
        for (let card of cards) {
            flipper.addFlipped({
                element: card,
                flipId: card.id,
                spring: springName,
                shouldFlip: false,
                onExit: exitSpring
            })
        }

        flipper.recordBeforeUpdate();

        if (append) {
            this.content.innerHTML += content;
        } else {
            this.content.innerHTML = content;
        }

        cards = this.content.children;
        for (let card of cards) {
            flipper.addFlipped({
                element: card,
                flipId: card.id,
                spring: springName,
                onAppear: appearSpring
            })
        }

        flipper.update();
    }

    /**
     * Show the loader icon and disable form wait response
     */
    showLoader() {
        const loader = this.form.querySelector('.js-loading');

        if (loader === null) {
            return;
        }

        this.form.classList.add('is-loading');
        loader.setAttribute('aria-hidden', false);
        loader.style.display = null;
    }

    /**
     * Hide the loader icon and disable form wait response
     */
    hideLoader() {
        const loader = this.form.querySelector('.js-loading');

        if (loader === null) {
            return;
        }

        this.form.classList.remove('is-loading');
        loader.setAttribute('aria-hidden', true);
        loader.style.display = 'none';
    }
}