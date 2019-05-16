<template>
    <div class="animated fadeIn">
        <b-card no-body>

            <div v-if="newCreated">
                <b-alert :show="5" variant="success" >Новая последовательность успешно создана.</b-alert>
            </div>


                <b-card-body>

                    <form @submit.stop.prevent="handleSave">

                    <b-card no-body>
                        <div slot="header">
                            <i class="fa fa-edit pr-2"></i><span>Основные настройки</span>
                            <div class="card-header-actions">
                                <b-link class="card-header-action btn-minimize" v-b-toggle.collapse1>
                                    <i class="icon-arrow-up"></i>
                                </b-link>
                            </div>
                        </div>

                        <b-collapse id="collapse1" visible>
                            <b-card-body>
                                    <b-row>
                                        <b-col sm="12">
                                            <b-row>
                                                <b-col sm="12">
                                                    <b-form-group :state="validateState('project_id')"
                                                                  aria-describedby="projectLiveFeedback">
                                                        <label>Проект</label>
                                                        <model-list-select
                                                                :class="{'form-control':true, 'is-invalid': validateState('project_id') === false,'search-select-invalid':validateState('project_id') === false}"
                                                                :list="projects"
                                                                v-model="item.project_id"
                                                                option-value="id"
                                                                option-text="name"
                                                                :selected-option="item.project_id"
                                                                placeholder="Выберите проект">
                                                        </model-list-select>
                                                        <b-form-invalid-feedback id="projectLiveFeedback" >
                                                            <template v-if="validateState('project_id') === false " v-for="nErr in errors.project_id" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="12">
                                                    <b-form-group>
                                                        <label for="nameInputId">Название</label>
                                                        <b-form-input :state="validateState('name')" aria-describedby="nameLiveFeedback" type="text" id="nameInputId" placeholder="Новая последовательность" v-model="item.name" ></b-form-input>
                                                        <b-form-invalid-feedback id="nameLiveFeedback" >
                                                            <template v-if="validateState('name') === false" v-for="nErr in errors.name" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="12">
                                                    <b-form-group>
                                                        <label for="api_aliasInputId">API Alias</label>
                                                        <b-form-input :state="validateState('api_alias')" aria-describedby="api_aliasLiveFeedback" type="text" id="api_aliasInputId" placeholder="example" v-model="item.api_alias"></b-form-input>
                                                        <b-form-invalid-feedback id="api_aliasLiveFeedback" >
                                                            <template v-if="validateState('api_alias') === false " v-for="nErr in errors.api_alias">
                                                                <span v-if="typeof nErr === 'object'" v-for="n in nErr" >{{n}}<br></span>
                                                                <span v-if="typeof nErr === 'string'" v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="12">
                                                    <b-form-checkbox-group stacked>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="customChk1" v-model="item.by_default">
                                                            <label class="custom-control-label" for="customChk1">По умолчанию</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="customChk2" v-model="item.for_nonworking_time">
                                                            <label class="custom-control-label" for="customChk2">Для нерабочего времени</label>
                                                        </div>
                                                    </b-form-checkbox-group>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                    </b-row>
                            </b-card-body>
                        </b-collapse>
                    </b-card>

                    <b-card no-body>
                        <div slot="header">
                            <i class="fa fa-edit pr-2"></i><span>Редактор цепочки</span>
                            <div class="card-header-actions">
                                <b-link class="card-header-action btn-minimize" v-b-toggle.collapse2>
                                    <i class="icon-arrow-up"></i>
                                </b-link>
                            </div>
                        </div>

                        <b-collapse id="collapse2" visible>
                            <b-card-body>
                                <sequence-visual-edit
                                    v-model="item.options"
                                    :projectId = "item.project_id"
                                >
                                </sequence-visual-edit>
                            </b-card-body>
                        </b-collapse>
                    </b-card>

                    <b-row>
                        <b-col sm="12">
                            <div class="d-flex">
                                <b-button class="btn btn-danger ml-auto" @click="handleCancel">Отмена</b-button>
                                <b-button class="btn btn-success success-btn"   @click="handleSave">Сохранить</b-button>
                            </div>
                        </b-col>
                    </b-row>

                    </form>

                </b-card-body>

        </b-card>
    </div>
</template>


<script>
    import { vSwitch, vCase, vDefault } from 'v-switch-case';
    import axios from 'axios';
    import { ModelListSelect } from 'vue-search-select';
    import Button from "bootstrap-vue/es/components/button/button";
    import SequenceVisualEdit from "./components/SequenceVisualEdit";

    export default {
        name: "SequenceForm",
        components: {
            SequenceVisualEdit,
            Button,
            ModelListSelect,
        },
        directives: {
            'switch': vSwitch,
            'case': vCase,
            'default': vDefault
        },
        updated(){
            if(this.$route.params && this.$route.params.newCreated){
                this.newCreated = true;
                delete this.$route.params.newCreated;
                this.$router.replace({ params :this.$route.params });
            }
        },
        mounted() {
            this.loadProjects();

            if(this.$route.params && this.$route.params.id !== undefined) {

                this.formTitle = "Редактирование";
                this.getSequence(this.$route.params.id);
                this.action = "update";
            }else {
                this.formTitle = "Новый проект";
                this.action = "create";
                this.item = {
                    name: "",
                    options: {
                        tasks: []
                    },
                    api_alias: "",
                    project_id: null,
                    by_default: null,
                    for_nonworking_time: null,
                }
            }
        },
        data: () => {
            return {
                newCreated:false,
                modalRemoveKeyShow:false,
                action:"create",
                errors:{},
                formTitle: "Новая последовательность",
                item: {
                    options: {
                        tasks: []
                    },
                },
                clients:[],
                projects:[],
            }
        },
        methods: {
            loadProjects() {
                axios.get(configs.apiUrl + "/select/projects/").then(response=>{
                    if(response.data.success === true){
                        this.projects = response.data.Projects;
                    }
                    else{
                        this.projects = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            selectFullName (v) {
                return `${v.last_name} ${v.first_name} ${v.father_name || ''}`
            },
            toggleModal() {
              this.selectedMessenger = null;
              this.modalShow = !this.modalShow;
            },
            handleOk(evt) {
                evt.preventDefault();
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            handleSubmit () {
                this.$refs.modal.hide()
            },
            handleSave (evt) {
                evt.preventDefault();
                if(this.$route.params && this.$route.params.id !== undefined) {
                    this.updateSequence(this.item.id);
                }else{
                    this.newSequence();
                }
                //this.$router.push({ path: '/ai-system/projects' });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            getSequence(id){
                axios.get(configs.apiUrl + "/sequences/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Sequence;
                        if( ! this.item.options){
                            this.item.options = {
                                tasks: []
                            };
                        }
                    }
                    else {
                        this.item = {};
                    }
                }).catch(e => {
                    //console.error(e);
                })
            },
            newSequence(){
                let dataToSave = Object.assign({},this.item);
                axios.post(configs.apiUrl + "/sequences",dataToSave).then(response=>{
                    if(response.data.success === true){
                        this.errors = [];
                        this.item.id = response.data.Sequence.id;
                        this.action = "update";
                        this.formTitle = "Редактирование";
                        this.$router.push({ name: 'sequence.form',params:{id:this.item.id,newCreated:true} });
                    }else{

                    }
                }).catch(e => {
                    let { data } = e.response;
                    if(data.errorType !== undefined && data.errorType === "VALIDATION_ERROR")  {
                       this.errors = data.errors;
                    }
                    else{
                        //console.error(data.errors);
                    }
                })
            },
            updateSequence(id){
                let dataToSave = Object.assign({},this.item);
                axios.put(configs.apiUrl + "/sequences/" + id,dataToSave).then(response=>{
                    if(response.data.success === true){
                        this.item = response.data.Sequence;
                        this.$router.push({ name: 'sequence' });
                    }else{

                    }
                }).catch(e => {
                    let { data } = e.response;
                    if(data.errorType !== undefined && data.errorType === "VALIDATION_ERROR")  {
                        this.errors = data.errors;
                    }
                    else{
                        //console.error(data.errors);
                    }
                })
            },
        }
    }
</script>

<style scoped>
    .success-btn {
        margin-left: 10px;
    }
    .messenger-input-group-prepend{
        width: 50px;
        padding-top:5px;
    }
    .btn-new-messenger {
        cursor:pointer;
        position: absolute;
        padding-top: 5px;
        padding-left: 5px
    }
    .btn-remove-messenger{
        position: absolute;
        margin-top: 37px;
    }
    .keys_block{
        margin-bottom: 20px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
    .custom-icon{
        margin-top: 30px;
        padding-left: 70px;
    }
</style>