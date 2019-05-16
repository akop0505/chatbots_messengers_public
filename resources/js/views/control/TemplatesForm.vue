<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col lg="12">
                <transition name="fade">
                    <b-card no-body v-if="show">
                        <div slot="header">
                            <i class="fa fa-edit"></i> {{formTitle}}
                            <div class="card-header-actions">
                                <b-link class="card-header-action btn-minimize" v-b-toggle.collapse1>
                                    <i class="icon-arrow-up"></i>
                                </b-link>
                            </div>
                        </div>
                        <b-collapse id="collapse1" visible>
                            <b-card-body>
                                <b-row>
                                    <b-col sm="6">
                                        <b-form-group :state="validateState('project_id')" aria-describedby="projectIdLiveFeedback">
                                            <label>Проект</label>
                                            <template>
                                                <!-- object value -->
                                                <list-select
                                                        :class="{'form-control':true, 'is-invalid': validateState('project_id') === false,'search-select-invalid':validateState('project_id') === false}"
                                                        :list="projects"
                                                        :selected-item="selected.project"
                                                        option-value="id"
                                                        option-text="name"
                                                        @select="onSelectProject"
                                                        placeholder="Выберите проект">
                                                </list-select>
                                            </template>
                                            <b-form-invalid-feedback id="projectIdLiveFeedback" >
                                                <template v-if="validateState('project_id') === false " v-for="nErr in errors.project_id" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                       <!-- <b-form-group label="Страна" label-for="countryInputId">
                                            <b-input-group>
                                                <b-form-input placeholder="RU" id="countryInputId" type="text" autocomplete="Страна" v-model="item.country" :state="validateState('country')" aria-describedby="countryLiveFeedback"></b-form-input>
                                                <b-form-invalid-feedback id="countryLiveFeedback" >
                                                    <template v-if="validateState('country') === false " v-for="nErr in errors.country" >
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-input-group>
                                        </b-form-group>-->
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group label="Название рассылки" label-for="nameInputId">
                                            <b-input-group>
                                                <b-form-input placeholder="Новая рассылка" id="nameInputId" type="text" autocomplete="Название" v-model="item.name" :state="validateState('name')" aria-describedby="nameLiveFeedback"></b-form-input>
                                                <b-form-invalid-feedback id="nameLiveFeedback" >
                                                    <template v-if="validateState('name') === false " v-for="nErr in errors.name" >
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-input-group>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group >
                                            <label for="countryInputId">Язык</label>
                                            <b-form-select
                                                    :state="validateState('country')"
                                                    :aria-describedby="'countryLiveFeedback'"
                                                    v-model="item.country"
                                                    class="mb-3"
                                                    id="countryInputId">
                                                <option :value="null" selected>Выберите язык</option>
                                                <option v-for="language in languages" :value="language.code" v-text="language.name"></option>
                                            </b-form-select>
                                            <b-form-invalid-feedback :id="'countryLiveFeedback'" >
                                                <template v-if="validateState('country') === false " v-for="nErr in errors['country']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group >
                                            <label for="eventInputId">Событие</label>
                                            <b-form-select
                                                    :state="validateState('event_id')"
                                                    :aria-describedby="'event_idLiveFeedback'"
                                                    v-model="item.event_id"
                                                    class="mb-3"
                                                    id="eventInputId">
                                                <option :value="null" selected>Выберите событие</option>
                                                <option v-for="event in events" :value="event.id" v-text="event.name"></option>
                                            </b-form-select>
                                            <b-form-invalid-feedback :id="'event_idLiveFeedback'" >
                                                <template v-if="validateState('event_id') === false " v-for="nErr in errors['event_id']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="receiverInputId">Получатель</label>
                                            <b-form-select
                                                    :state="validateState('receiver_id')"
                                                    :aria-describedby="'receiver_idLiveFeedback'"
                                                    v-model="item.receiver_id"
                                                    class="mb-3"
                                                    id="receiverInputId">
                                                <option :value="null" selected>Выберите получателя</option>
                                                <option v-for="receiver in receivers" :value="receiver.id" v-text="receiver.name"></option>
                                            </b-form-select>
                                            <b-form-invalid-feedback :id="'receiver_idLiveFeedback'" >
                                                <template v-if="validateState('receiver_id') === false " v-for="nErr in errors['receiver_id']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="manuInputId">Меню</label>
                                            <b-form-select
                                                    :state="validateState('menu_id')"
                                                    :aria-describedby="'menu_idLiveFeedback'"
                                                    v-model="item.menu_id"
                                                    class="mb-3"
                                                    id="menuInputId">
                                                <option :value="null" selected>Выберите меню</option>
                                                <option v-for="menu in menus" :value="menu.id" v-text="menu.name"></option>
                                            </b-form-select>
                                            <b-form-invalid-feedback :id="'menu_idLiveFeedback'" >
                                                <template v-if="validateState('menu_id') === false " v-for="nErr in errors['menu_id']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col sm="12">
                                        <label for="textarea1ID">Текст сообшения</label>
                                        <textarea
                                                :class="{'form-control':true, 'is-invalid': validateState('text') === false,'search-select-invalid':validateState('text') === false}"

                                                id="textarea1ID"
                                                         :state="validateState('text')"
                                                         :aria-describedby="'textLiveFeedback'"
                                                         v-model="item.text"
                                                         placeholder="Введите текст сообшения"
                                                         :rows="6"
                                                         ref="textareaText"
                                                         @blur="onBlur"
                                        >
                                        </textarea>
                                        <b-form-invalid-feedback :id="'textLiveFeedback'" >
                                            <template v-if="validateState('text') === false " v-for="nErr in errors['text']">
                                                <span  v-text="nErr"></span><br>
                                            </template>
                                        </b-form-invalid-feedback>
                                    </b-col>
                                </b-row>
                                <br>
                                <b-row>
                                    <b-col sm="12">
                                        <template v-for="parameter in parameters ">
                                            <b-button  variant="outline-secondary" class="parameters" @click="appendParameter(parameter)">
                                                {{ parameter.name }}
                                            </b-button>{{" "}}
                                        </template>
                                    </b-col>
                                </b-row>
                                <br>
                                <div class=" d-flex form-actions">
                                    <b-button type="button" variant="danger" class="ml-auto" @click="cancelHandle">Отмена</b-button>
                                    <b-button type="submit" variant="success" class="success-btn" @click="saveHandle">Сохранить</b-button>
                                </div>
                            </b-card-body>
                        </b-collapse>
                    </b-card>
                </transition>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import { menus } from '../../testData/data';
    import { ListSelect } from 'vue-search-select';

    export default {
        name: 'TemplatesForm',
        components: {
            ListSelect
        },
        mounted() {
            this.loadLanguages();
            this.loadProjects();
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование шаблона";
                this.loadTemplate(this.$route.params.id);
            }else {
                this.formTitle = "Создание шаблона";
                this.item = {
                    country:null,
                    name:"",
                    text:"",
                    project_id:null,
                    menu_id:null,
                    event_id:null,
                    receiver_id:null,
                    payment_type:null,
                }
            }
        },
        data () {
            return {
                show: true,
                item:{},
                items:[],
                languages:[],
                projects:[],
                events:[],
                receivers:[],
                menus:[],
                parameters:[],
                selected:{
                    project:{},
                    event:{},
                    receiver:{},
                    parameters:{},
                },
                errors:{},
                formTitle: "Редактирование шаблона",
                textSelectionStart: null
            }
        },
        methods: {
            onBlur(){
                let textarea = this.$refs.textareaText;
                this.textSelectionStart = textarea.selectionStart;
                console.log(this.textSelectionStart);
            },
            saveHandle(){
                if(this.item && this.item.id !== undefined) {
                    this.updateHandle();
                }else {
                    this.createHandle();
                }
            },
            loadLanguages() {
                axios.get(configs.apiUrl + "/languages").then(response=>{
                    if(response.data.success === true){
                        this.languages = response.data.Languages;
                    }
                    else{
                        this.languages = [];
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
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
            loadMore() {
                axios.all([
                    axios.get(configs.apiUrl + "/select/events?project_id=" + this.selected.project.id),
                    axios.get(configs.apiUrl + "/select/receivers?project_id=" + this.selected.project.id),
                    axios.get(configs.apiUrl + "/select/menus?project_id=" + this.selected.project.id),
                    axios.get(configs.apiUrl + "/select/variables?project_id=" + this.selected.project.id),
                ])
                    .then(axios.spread((eventsRes, receiversRes,menusRes,variablesRes) => {
                        this.events = eventsRes.data.Events;
                        this.receivers = receiversRes.data.Receivers;
                        this.menus = menusRes.data.Menus;
                        this.parameters = variablesRes.data.Parameters;
                    }))
                    .catch(e => {
                        console.log(e.response);
                    });
            },
            onSelectProject(item) {
                this.$set(this.item, 'project_id', item.id);
                if(this.selected.project.id !== item.id){
                    this.$set(this.item, 'menu_id', null);
                    this.$set(this.item, 'event_id', null);
                    this.$set(this.item, 'receiver_id', null);
                    this.$set(this.item, 'payment_type', null);
                }
                this.selected.project = item;
                this.loadMore();
            },
            /*onSelectEvent(item) {
                this.selected.event = item;
            },
            onSelectReceiver(item) {
                this.selected.receiver = item;
            },
            onSelectMenu(item) {
                this.selected.receiver = item;
            },*/
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            appendParameter(parameter) {
                if(this.item.text === undefined){
                    this.$set(this.item, 'text', "");
                }
                let text = this.item.text;
                if(this.textSelectionStart === null){
                    text += parameter.variable;
                    this.textSelectionStart += (text.length);
                }else{
                    text = text.slice(0, this.textSelectionStart) + parameter.variable + text.slice(this.textSelectionStart);
                    this.textSelectionStart += (parameter.variable.length);
                }

                this.$set(this.item, 'text', text);
            },
            click () {
                // do nothing
            },
            loadTemplate(id){
                axios.get(configs.apiUrl + "/templates/"+id).then(response=>{
                    if(response.data.success === true){
                        this.item = response.data.Template;
                        let project = this.projects.find(v=>{return v.id == this.item.project_id});
                        this.selected.project.id = project.id;
                        this.onSelectProject(project);
                    }
                }).catch(e => {
                    let response = e.response;
                    if(response.data.errorType === "VALIDATION_ERROR"){
                        this.errors = response.data.errors;
                    }
                    console.log(response);
                });
            },
            createHandle() {
                axios.post(configs.apiUrl + "/templates/",this.item).then(response=>{
                    if(response.data.success === true){
                        this.$router.push({name:'template'});
                        this.item = {};
                    }
                }).catch(e => {
                    let response = e.response;
                    if(response.data.errorType === "VALIDATION_ERROR"){
                        this.errors = response.data.errors;
                    }
                    console.log(response);
                });
                //this.$router.push({ path: '/control/templates' });
            },
            updateHandle() {
                axios.put(configs.apiUrl + "/templates/" + this.item.id,this.item).then(response=>{
                    if(response.data.success === true){
                        this.$router.push({name:'template'});
                        this.item = {};
                    }

                }).catch(e => {
                    let response = e.response;
                    if(response.data.errorType === "VALIDATION_ERROR"){
                        this.errors = response.data.errors;
                    }
                    console.log(response);
                });
            },
            cancelHandle() {
                this.$router.go(-1);
            },
        }
    }
</script>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.5s;
    }
    .fade-enter,
    .fade-leave-to {
        opacity: 0;
    }
    .parameters{
        margin-bottom: 2px;
        margin-top: 2px;
    }
    .success-btn{
        margin-left: 10px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
</style>
