<template>
    <div class="animated fadeIn">
        <b-card no-body>
            <div slot="header">
                <i class="fa fa-edit"></i><span v-text="formTitle"></span>
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
                            <form @submit.stop.prevent="handleSave">
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
                                            <b-form-input type="text" id="nameInputId" placeholder="Название" v-model="item.name"
                                                          :state="validateState('name')"
                                                          aria-describedby="nameLiveFeedback"
                                            ></b-form-input>

                                            <b-form-invalid-feedback id="nameLiveFeedback" >
                                                <template v-if="validateState('name') === false " v-for="nErr in errors.name" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="callback_urlInputId">Функция обратного вызова</label>
                                            <b-form-input type="text" id="callback_urlInputId" placeholder="http://www.example.com" v-model="item.callback_url"
                                                          :state="validateState('callback_url')"
                                                          aria-describedby="callback_urlLiveFeedback"></b-form-input>
                                            <b-form-invalid-feedback id="callback_urlLiveFeedback" >
                                                <template v-if="validateState('callback_url') === false " v-for="nErr in errors.callback_url" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <h4>Варианты <i class="nav-icon icon-plus" @click="addMenuItems()"></i></h4>

                                        <span class="text-danger" v-if="validateState('MenuItems') === false " v-for="nErr in errors['MenuItems']" >
                                            <span  v-text="nErr"></span><br>
                                        </span>
                                        <b-row>
                                            <template v-for="(variant,key) in item.MenuItems">
                                                <b-col sm="1">
                                                    <b-form-group>
                                                        <label :for="'menu_item_pointInputId' + key">Пункт</label>
                                                        <b-form-input type="text" :id="'menu_item_pointInputId'+ key" placeholder="Пункт" v-model="variant.point"
                                                                      :state="validateState('MenuItems.'+key+'.point')"
                                                                      :aria-describedby="'pointLiveFeedback' + key"
                                                        ></b-form-input>

                                                        <b-form-invalid-feedback :id="'pointLiveFeedback' + key" >
                                                            <template v-if="validateState('MenuItems.'+key+'.point') === false " v-for="nErr in errors['MenuItems.'+key+'.point']" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="3">
                                                    <b-form-group>
                                                        <label :for="'menu_item_nameInputId' + key">Вариант</label>
                                                        <b-form-input type="text" :id="'menu_item_nameInputId' + key" placeholder="Вариант" v-model="variant.name"
                                                                      :state="validateState('MenuItems.'+key+'.name')"
                                                                      :aria-describedby="'nameLiveFeedback' + key"></b-form-input>
                                                        <b-form-invalid-feedback :id="'nameLiveFeedback' + key" >
                                                            <template v-if="validateState('MenuItems.'+key+'.name') === false " v-for="nErr in errors['MenuItems.'+key+'.name']" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="2">
                                                    <b-form-group>
                                                        <label :for="'menu_item_callback_urlInputId' + key">Функция обратного вызова</label>
                                                        <b-form-input type="text" :id="'menu_item_callback_urlInputId' + key" placeholder="http://www.example.com" v-model="variant.callback_url"
                                                                      :state="validateState('MenuItems.'+key+'.callback_url')"
                                                                      :aria-describedby="'callback_urlLiveFeedback' + key +' '+'callback_urlLiveHelp' + key "></b-form-input>

                                                        <b-form-invalid-feedback :id="'callback_urlLiveFeedback' + key" >
                                                            <template v-if="validateState('MenuItems.'+key+'.callback_url') === false " v-for="nErr in errors['MenuItems.'+key+'.callback_url']" >
                                                                <span  v-text="nErr"></span><br>
                                                            </template>
                                                        </b-form-invalid-feedback>
                                                        <b-form-text :id="'callback_urlLiveHelp' + key">
                                                            <template v-if="callbackUrlRequired(variant)">
                                                                <span class="text-warning" v-text="'Обязательно заполните данное поле'"></span><br>
                                                            </template>
                                                        </b-form-text>
                                                    </b-form-group>
                                                </b-col>
                                                <b-col sm="1" class="autoreply-col">
                                                    <b-form-checkbox-group stacked id="autoReplyInputId">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" :id="'menu_item_auto_reply_InputId' + key" v-model="variant.auto_reply">
                                                            <label class="custom-control-label" :for="'menu_item_auto_reply_InputId' + key">Автоответ</label>
                                                        </div>
                                                    </b-form-checkbox-group>
                                                </b-col>
                                                <b-col sm="2" class="autoreply-settings-col">
                                                    <b-button class="btn btn-success success-btn" @click="autoReplySettings(variant)" :disabled="!variant.auto_reply">Настроить автоответ</b-button>
                                                </b-col>
                                                <b-col sm="2" class="autoreply-col">
                                                    <b-form-checkbox-group stacked id="autoReplyInputId">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" :id="'menu_item_default_InputId' + key" v-model="variant.default"
                                                                   @click="checkboxDefault(key)">
                                                            <label class="custom-control-label" :for="'menu_item_default_InputId' + key">По умолчанию</label>
                                                        </div>
                                                    </b-form-checkbox-group>
                                                </b-col>
                                                <b-col sm="1" class="remove-col">
                                                    <span>
                                                        <i class="nav-icon icon-close" @click="removeMenuItems(key)"></i>
                                                    </span>

                                                </b-col>
                                            </template>
                                        </b-row>
                                    </b-col>
                                    <b-col sm="12" v-if="item.PresentReplies">
                                        <hr>
                                        <present-reply-form :points="getPoints()" v-bind:presentReplies="item.PresentReplies" v-bind:errors="errors"></present-reply-form>
                                    </b-col>
                                    <b-col sm="12">
                                        <hr>
                                        <div class="d-flex">
                                            <b-button class="btn btn-danger  ml-auto" @click="handleCancel">Отмена</b-button>
                                            <b-button class="btn btn-success success-btn"   @click="handleSave">Сохранить</b-button>
                                        </div>
                                    </b-col>
                                </b-row>
                            </form>
                        </b-col>
                    </b-row>
                </b-card-body>
            </b-collapse>
        </b-card>
        <b-modal id="autoReplySettings"
                 ref="autoReplySettingsModal"
                 title="Новый канал"
                 @ok="handleOk"
                 ok-title="Ok"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            <label for="autoreplyTypeSelectId">Тип ответа</label>
            <b-form-select  class="mb-3" :id="'autoreplyTypeSelectId'" v-model="menuItemSettings.reply_type">
                <option :value="null" selected>Выберите тип  ответа</option>
                <option value="present">Предустановленный ответ</option>
                <option value="template">Шаблон сообщения</option>
                <option value="logic_template">Шаблон сообщения (с логикой)</option>
                <option value="none">Ничего</option>
            </b-form-select>


            <div v-if="(['template', 'logic_template']).includes(menuItemSettings.reply_type)">
                <label for="autoreplyTemplateSelectId">Шаблон <span class="text-muted" v-if="menuItemSettings.reply_type === 'logic_template'">(TRUE)</span></label>
                <b-form-select  class="mb-3" :id="'autoreplyTemplateSelectId'" v-model="menuItemSettings.template_id">
                    <option :value="null">Выберите шаблон</option>
                    <option v-for="(template,key) in templatesForReplySettings" :value="template.id" v-text="template.name"></option>
                </b-form-select>
            </div>

            <div v-if="menuItemSettings.reply_type === 'logic_template'">
                <label for="autoreplyFalseTemplateSelectId">Шаблон <span class="text-muted">(FALSE)</span></label>
                <b-form-select  class="mb-3" :id="'autoreplyFalseTemplateSelectId'" v-model="menuItemSettings.false_template_id">
                    <option :value="null">Выберите шаблон</option>
                    <option v-for="(template,key) in templatesForReplySettings" :value="template.id" v-text="template.name"></option>
                </b-form-select>
            </div>
        </b-modal>
    </div>
</template>


<script>
    import { menus } from  '../../testData/data';
    import { ModelListSelect } from 'vue-search-select';
    import PresentReplyForm from './components/PresentReplyForm';

    export default {
        name: "SelfManagementMenuForm",
        components: {
            ModelListSelect,
            PresentReplyForm
        },
        watch:{
            item : {
                handler: function(newVal,oldVal) {
                    console.log(newVal);
                },
                deep: true
            }

        },
        mounted() {
            this.loadProjects();
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование";
                this.getItem(this.$route.params.id);
            }else {
                this.formTitle = "Новое меню";
                this.item = {
                    project_id:null,
                    name:"",
                    description:"",
                    MenuItems:[],
                    PresentReplies:[]
                };
            }
        },
        data: () => {
            return {
                projects:[],
                formTitle: "Новое меню",
                item: {
                    MenuItems:[],
                    PresentReplies:[]
                },
                errors:{},
                modalShow: false,
                menuItemSettings: {},
                templatesForReplySettings: null
            }
        },
        methods: {
            checkboxDefault(key){
                console.log(this.item.MenuItems);
                for (const prK in this.item.MenuItems){
                    this.$set(this.item.MenuItems[prK],'default',false);
                }
                this.$set(this.item.MenuItems[key],'default',true);
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            callbackUrlRequired(variant){
                if(variant.auto_reply && variant.reply_type === 'template'){
                    return true;
                }
                return false;
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
            getItem(id) {
                axios.get(configs.apiUrl + "/menu/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Menu;
                        console.log(this.item.PresentReplies.length);
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            getTemplatesToSelect(project_id) {
                if(this.templatesForReplySettings == null){
                    axios.get(configs.apiUrl + "/select/templates/?project_id=" + project_id).then(response=>{
                        if(response.data.success === true) {
                            this.templatesForReplySettings = response.data.Templates;
                        }
                        else{
                            //console.error(response.data);
                        }
                    }).catch(e=>{
                        //console.error(e);
                    });
                }
            },
            handleSave (evt) {
                evt.preventDefault();
                if(this.$route.params && this.$route.params.id === undefined) {
                    this.newItem();
                }
                else{
                    this.updateItem(this.$route.params.id)
                }
            },
            newItem(){
                console.log(this.item);
                axios.post(configs.apiUrl + "/menu", this.item).then(response=>{
                    if(response.data.success === true) {
                        //this.item = response.data.receivers;
                        this.$router.push({ name: 'self-management-menu' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error("error = ",e);
                    this.errors = e.response.data.errors;
                });
            },
            updateItem(id){
                axios.put(configs.apiUrl + "/menu/" + id, this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Menu;
                        this.$router.push({ name: 'self-management-menu' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error Response = ",e.response);
                    this.errors = e.response.data.errors;
                });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            addMenuItems(){
                this.item.MenuItems.push({
                    point: "",
                    name: "",
                    callback_url: "",
                    auto_reply: false,
                    reply_type: "present",
                    template_id: null,
                    false_template_id: null,
                    default: false
                });
            },
            removeMenuItems(index) {
                let point = this.item.MenuItems[index].point;
                this.item.MenuItems.splice(index,1);
                this.item.PresentReplies = this.item.PresentReplies.filter((val)=>{
                    return val.point != point;
                });
            },
            autoReplySettings(itemMenu) {
                this.getTemplatesToSelect(this.item.project_id);
                this.modalShow = true;
                this.menuItemSettings = itemMenu;
                // this.menuItemSettings = _.clone(itemMenu, true);
            },
            handleOk(){
                this.menuItemSettings
                this.modalShow = false;
            },
            getPoints(){
                // console.log(this.item.MenuItems);
                return this.item.MenuItems.map(function (val) {
                    return val.point
                });
            }
        }
    }
</script>

<style scoped>
    .success-btn{
        margin-left: 10px;
    }
    .remove-col{
        padding-top: 38px;
    }
    .autoreply-col{
        padding-top: 34px;
    }
    .autoreply-settings-col{
        padding-top: 29px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
</style>