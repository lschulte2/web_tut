<template>
    <div class="image-container">
        <div v-show="showPopup" class="popup" ref="popup">
            <form @submit.prevent="finishPendingAnnotation">
                <input
                    class="form-control"
                    type="text"
                    placeholder="Label name"
                    v-model="pendingLabel"
                    ref="labelInput"
                    @keydown.esc="discardPendingAnnotation"
                >
            </form>
        </div>
    </div>
    </template>
    
    <script>
    import "ol/ol.css";
    import Draw from 'ol/interaction/Draw';
    import Feature from "ol/Feature";
    import ImageLayer from 'ol/layer/Image';
    import ImageStatic from 'ol/source/ImageStatic';
    import Map from 'ol/Map';
    import Overlay from "ol/Overlay";
    import Point from 'ol/geom/Point';
    import Projection from 'ol/proj/Projection';
    import VectorLayer from "ol/layer/Vector";
    import VectorSource from "ol/source/Vector";
    import View from 'ol/View';
    import {getCenter} from 'ol/extent';
    
    const imageLayer = new ImageLayer();
    const vectorSource = new VectorSource({wrapX: false});
    const vectorLayer = new VectorLayer({source: vectorSource});
    const draw = new Draw ({
        source: vectorSource,
        type: 'Point',
    })
    const popup = new Overlay({});
    const map = new Map({
        layers: [imageLayer, vectorLayer],
    });
    
    export default {
        props: {
            image: {
                type: Image,
            },
            annotations: {
                type:Array,
            },
        },
        data() {
            return {
                pendingAnnotation:null,
                pendingLabel:'',
            };
        },
        computed: {
            showPopup() {
                return this.pendingAnnotation!==null;
            },
        },
        methods: {
            updateImage(image) {
                const extent = [0, 0, image.width, image.height];
                const projection = new Projection({
                    code: 'image',
                    units: 'pixels',
                    extent: extent,
                });
    
                imageLayer.setSource(new ImageStatic({
                    url: image.src,
                    projection: projection,
                    imageExtent: extent,
                }));
    
                map.setView(new View({
                    projection: projection,
                    center: getCenter(extent),
                    zoom: 1,
                    maxZoom: 8,
                }));
                vectorSource.clear();
                vectorSource.un('addfeature', this.handleFeatureAdded);
                vectorSource.addFeatures(this.annotations.map(annotation => {
                    return new Feature (new Point([annotation.x, annotation.y]));
                }));
                vectorSource.on('addfeature',this.handleFeatureAdded);
            },
            handleFeatureAdded(event) {
                if (this.pendingAnnotation) {
                    this.discardPendingAnnotation();
                }
                popup.setPosition(event.feature.getGeometry().getCoordinates());
                this.pendingAnnotation = event.feature;
                this.$nextTick(() => this.$refs.labelInput.focus()); 
            },
            discardPendingAnnotation() {
                vectorSource.removeFeature(this.pendingAnnotation);
                this.pendingAnnotation=null;
                this.pendingLabel=null;
            },
            finishPendingAnnotation() {
                let [x,y] = this.pendingAnnotation
                    .getGeometry()
                    .getCoordinates()
                    .map(x=> Math.round(x*100)/100);
                let label = this.pendingLabel;
                this.$emit('annotation', {x,y,label});
                this.pendingAnnotation=null;
                this.pendingLabel=null;
            }
        },
        watch: {
            image(image) {
                this.updateImage(image);
            },
        },
        created() {
            map.addInteraction(draw);
            map.addOverlay(popup);
            vectorSource.on('addfeature',this.handleFeatureAdded);
        },
        mounted() {
            map.setTarget(this.$el);
            popup.setElement(this.$refs.popup);
        },
    };
    </script>
    
    <style lang="scss" scoped>
    .image-container {
        height: calc(100vh - 55px);
    }

    .popup {
        position: absolute;
        transform: translateY(-50%) translateX(12px);
        width: 200px;
    }
    </style>