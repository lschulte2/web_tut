/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import ImageContainer from './components/ImageContainer.vue';
import sha256 from 'crypto-js/sha256';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({
    data() {
        return {
            image: null,
            imageId: null,
            loading: false,
            annotations : [],
        };
    },
    components: {
        ImageContainer: ImageContainer,
    },
    methods: {
        openImage() {
            this.$refs.fileInput.click();
        },
        loadImage(event) {
            this.loading=true;
            const [file] = event.target.files;
            if (file) {
                const reader = new FileReader();
                reader.addEventListener("load",()=>{
                    const image = new Image;
                    image.addEventListener('load', this.handleImageLoaded);
                    image.src=reader.result;
                });
                reader.readAsDataURL(file);
            }
        },
        handleImageLoaded(event) {
            let hash =sha256(event.target.src).toString();
            axios.post('api/images', {hash})
                .then(response => {
                    this.imageId = response.data.id;
                    this.annotations=response.data.annotations || [];
                })
                .then(() => this.image = event.target)
                .finally(() => this.loading = false);
        },
        handleNewAnnotation(annotation) {
            if (!this.imageId) {
                return;
            }
            this.loading=true;
            axios.post(`api/images/${this.imageId}/annotations`,annotation)
                .finally(() => this.loading=false);
        }
    },
    mounted() {
        this.$refs.fileInput.addEventListener('change',this.loadImage);
    },
});

import ExampleComponent from './components/ExampleComponent.vue';
import axios from 'axios';
app.component('example-component', ExampleComponent);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
