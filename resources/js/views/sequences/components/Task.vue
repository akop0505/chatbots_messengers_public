<template>
    <list-group-item :active="showModalTaskOptions || showModalTaskWaitOptions">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button v-handle type="button" class="handle btn btn-secondary mr-2 disabled" disabled>
                    <i class="fa fa-arrows-v"></i>
                </button>

                <datetime v-model="bufferWaitOptions" input-class="d-none" type="time" ref="datatime"
                    :phrases="{ok: 'OK', cancel: 'Отмена'}"
                    >
                </datetime>

                <button type="button" class="btn btn-secondary mx-2"
                    v-if="form"
                    @click="handleShowWaitOptions">
                    <i class="fa fa-clock"></i>
                    <span v-if="item.wait !== ''">
                        Ожидание: {{item.wait}}
                        <b-button aria-label="Close" class="close"
                              ref="removeWait"
                            @click="handleRemoveWait"
                            >×</b-button>
                    </span>
                    <span v-else>Без ожидания</span>

                </button>

                <span class="mx-2 d-flex">
                    <i class="font-2xl pr-2"
                       v-bind:class="item.icon"></i>
                    {{title}}
                </span>
            </div>
            <div class="btn-group" role="group" aria-label="Third group">
                <b-button class="ml-auto"
                          variant="primary"
                          v-if="form"
                          @click="handleShowOptions"><i class="fa fa-cog"></i></b-button>
                <b-button class="ml-auto"
                          variant="danger"
                          @click="handleRemove"><i class="fa fa-trash"></i></b-button>
            </div>
        </div>

        <b-modal id="modalTaskOptions"
                 ref="modal"
                 :title="'Настройки задачи: '+item.title"
                 ok-title="Применить"
                 @ok="handleSaveOptions"
                 cancel-title="Отмена"
                 @cancel="handleCancelOptions"
                 v-model="showModalTaskOptions"
        >
            <component v-bind:is="form" ref="form"
                       v-model="bufferOptions"
                       :projectId="projectId">
            </component>
        </b-modal>
    </list-group-item>
</template>


<script>

    import { sequencesTaskWaitTypes } from '../../../configs/sequences';
    import { ElementMixin, HandleDirective } from 'vue-slicksort';
    import ListGroupItem from "bootstrap-vue/es/components/list-group/list-group-item";
    import Sms from "./taskOptions/Sms";
    import Default from "./taskOptions/Default";
    import Wait from "./taskOptions/Wait";
    import { Datetime } from 'vue-datetime';
    import { datetimeFromISO } from 'vue-datetime/src/util'

    export default {
        components: {
            Wait,
            ListGroupItem,
            Datetime
        },
        mixins:     [ElementMixin, HandleDirective],
        directives: {
            handle: HandleDirective,
        },
        props: {
            'projectId': {},
            'value': {
                type: Object,
                required: true,
            },
        },
        data: function () {
            return {
                showModalTaskWaitOptions: false,
                showModalTaskOptions: false,
                bufferOptions: {},
                bufferWaitOptions: "",
            }
        },
        computed: {
            title: function () {
                switch (this.item.type) {
                    case 'day': return this.item.title + ' ' + this.item.index;
                    default: return this.item.title;
                }
            },
            form: function() {
                switch (this.item.type) {
                    case 'sms': return Sms;
                    case 'day': return undefined;
                    default: return Default;
                }
            },
            item: {
                get: function() {
                    if(this.value){
                        if( ! this.value.options){
                            Vue.set(this.value, 'options', {});
                        }
                        if( ! this.value.wait){
                            Vue.set(this.value, 'wait', "");
                        }
                        return this.value;
                    }else{return {};}
                },
                set: function(val){
                    this.bufferOptions = Object.assign({},val.options);

                    var now = new Date();
                    var date = val.wait.split(':');
                    now.setHours(date[0]);
                    now.setMinutes(date[1]);
                    this.bufferWaitOptions = now.toUTCString();
                    this.$emit('input', val);
                }
            },
            wait: {
                get: function() {
                    return datetimeFromISO(this.item.wait).setZone('local').toLocaleString({
                        hour: "numeric",
                        hour12: false,
                        minute: "2-digit"
                    });
                }
            }
        },
        watch: {
            bufferWaitOptions:  function(val) {
                if(val !== '') {
                    this.item.wait = datetimeFromISO(val).setZone('local').toLocaleString({
                        hour:   "numeric",
                        hour12: false,
                        minute: "2-digit"
                    });
                }else{
                    this.item.wait = val;
                }
            }
        },
        methods: {
            loadSequences() {
                axios.get(configs.apiUrl + "/select/sequences/").then(response=>{
                    if(response.data.success === true){
                        this.projects = response.data.Sequences;
                    }
                    else{
                        this.projects = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleRemove() {
                this.$parent.$emit('removeTask', this.index);
            },

            handleShowOptions() {
                this.bufferOptions = Object.assign({},this.item.options);
                this.showModalTaskOptions = true;
            },
            handleSaveOptions(evt) {
                evt.preventDefault();
                if(typeof (this.$refs.form.validate) === 'function') {
                    this.$refs.form.validate().then(() => {
                        this.item.options = Object.assign({}, this.bufferOptions);
                        this.$refs.modal.hide();
                    }).catch(e => {
                        console.log(e);
                    });
                }else{
                    this.item.options = Object.assign({}, this.bufferOptions);
                    this.$refs.modal.hide();
                }
            },
            handleCancelOptions() {
            },


            handleShowWaitOptions(event) {
                if(event.toElement == this.$refs.removeWait){return;} // если нажали удаление ожидания не открывать окно

                this.$refs.datatime.$el.getElementsByTagName('input')[0].click();
            },
            handleSaveWaitOptions(){
                this.item.wait = this.bufferWaitOptions;
            },
            handleCancelWaitOptions(){
            },
            handleRemoveWait(){
                this.bufferWaitOptions = "";
            }
        }
    }
</script>

<style scoped>
    .list-group-item, .list-group-item:hover{
        z-index: auto;
    }

    /deep/ #modalTaskOptions, /deep/ #modalWaitOptions {
        color: #23282c;
    }
</style>