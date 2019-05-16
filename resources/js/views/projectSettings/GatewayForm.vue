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
                                        <b-form-group>
                                            <label for="channelInputId">Канал</label>
                                            <b-form-select :state="validateState('messenger_id')"
                                                           aria-describedby="channelLiveFeedback"
                                                           v-model="item.messenger_id"
                                                           class="mb-3"
                                                           id="channelInputId" >
                                                <option :value="null" selected>Выберите канал</option>
                                                <option v-for="messenger in messengers" :value="messenger.id" v-text="messenger.name"></option>
                                            </b-form-select>

                                            <b-form-invalid-feedback id="channelLiveFeedback" >
                                                <template v-if="validateState('messenger_id') === false " v-for="nErr in errors.messenger_id" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="nameInputId">Название шлюза</label>
                                            <b-form-input :state="validateState('name')"
                                                          aria-describedby="nameLiveFeedback"
                                                          type="text"
                                                          id="nameInputId"
                                                          placeholder="Мой шлюз"
                                                          v-model="item.name"
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
                                            <label for="descriptionInputId">Описание шлюза</label>
                                            <b-form-textarea
                                                    :state="validateState('description')"
                                                    aria-describedby="descriptionLiveFeedback"
                                                    id="descriptionInputId"
                                                    placeholder="Описание шлюза"
                                                    v-model="item.description"
                                                    :rows="3"
                                                    :max-rows="6"
                                            ></b-form-textarea>
                                            <b-form-invalid-feedback id="descriptionLiveFeedback" >
                                                <template v-if="errors.description && errors.description.length" v-for="nErr in errors.description" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group >
                                            <label for="linkInputId">Ссылка на офф. сайт</label>
                                            <b-form-input
                                                    :state="validateState('link')"
                                                    aria-describedby="linkLiveFeedback"
                                                    type="text" id="linkInputId"
                                                    placeholder="http://www.example.com"
                                                    v-model="item.link"
                                            ></b-form-input>
                                            <b-form-invalid-feedback id="linkLiveFeedback" >
                                                <template v-if="errors.link && errors.link.length" v-for="nErr in errors.link" >
                                                    <span v-if="typeof nErr === 'object'" v-for="n in nErr">{{n}}<br></span>
                                                    <span v-if="typeof nErr === 'string'" v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-checkbox-group stacked id="byDefaultInputId">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="customChk1" v-model="item.by_default" checked>
                                                <label class="custom-control-label" for="customChk1">По умолчанию</label>
                                            </div>
                                        </b-form-checkbox-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <hr>
                                        <h4>Поля конфигураций <span class="btn-add-field"><i class="nav-icon icon-plus" @click="addConfigField()"></i></span></h4>
                                        <br>
                                        <b-row v-if="item.config && item.config.length > 0" v-for="(field,key) in item.config"  :key="field + key">
                                            <b-col sm="2">
                                                <b-form-group>
                                                    <!--<label :for="'fieldInputId' + key" :key="value + key" >Поле</label>-->
                                                    <b-form-input type="text"
                                                                  :id="'fieldInputId' + key"
                                                                  placeholder="Поле"
                                                                  v-model="field.field_name"
                                                                  :state="validateState('config.'+ key +'.field_name')"
                                                                  :aria-describedby="'field_nameLiveFeedback' + key"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback :id="'field_nameLiveFeedback' + key">
                                                        <template v-if="validateState('config.'+ key +'.field_name') === false " v-for="nErr in errors['config.'+ key +'.field_name']" >
                                                            <span  v-text="nErr"></span><br>
                                                        </template>
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </b-col>
                                            <b-col sm="3">
                                                <b-form-group>
                                                    <!--<label :for="'fieldInputId' + key" :key="value + key" >Поле</label>-->
                                                    <b-form-input type="text"
                                                                  :id="'placeholderInputId' + key"
                                                                  placeholder="Placeholder"
                                                                  v-model="field.placeholder"
                                                                  :state="validateState('config.'+ key +'.field_name')"
                                                                  :aria-describedby="'field_nameLiveFeedback' + key"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback :id="'placeholderLiveFeedback' + key">
                                                        <template v-if="validateState('config.'+ key +'.placeholder') === false " v-for="nErr in errors['config.'+ key +'.placeholder']" >
                                                            <span  v-text="nErr"></span><br>
                                                        </template>
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </b-col>
                                            <b-col sm="3">
                                                <b-form-group>
                                                    <!--<label :for="'fieldInputId' + key" :key="value + key" >Поле</label>-->
                                                    <b-form-input
                                                            type="text"
                                                            :id="'validationInputId' + key"
                                                            placeholder="Валидация"
                                                            v-model="field.validation"
                                                            :state="validateState('config.'+ key +'.validation')"
                                                            :aria-describedby="'validationLiveFeedback' + key"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback :id="'validationLiveFeedback' + key">
                                                        <template v-if="validateState('config.'+ key +'.validation') === false " v-for="nErr in errors['config.'+ key +'.validation']" >
                                                            <span  v-text="nErr"></span><br>
                                                        </template>
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </b-col>
                                            <b-col sm="3">
                                                <b-form-group>
                                                    <!--<label :for="'fieldInputId' + key" :key="value + key" >Поле</label>-->
                                                    <b-form-input
                                                            type="text"
                                                            :id="'messageInputId' + key"
                                                            placeholder="Сообщение"
                                                            v-model="field.message"
                                                            :state="validateState('config.'+ key +'.message')"
                                                            :aria-describedby="'messageLiveFeedback' + key"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback :id="'messageLiveFeedback' + key">
                                                        <template v-if="validateState('config.'+ key +'.message') === false " v-for="nErr in errors['config.'+ key +'.message']" >
                                                            <span  v-text="nErr"></span><br>
                                                        </template>
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </b-col>
                                            <b-col sm="1" class="remove-col">
                                                        <span>
                                                            <i class="nav-icon icon-close" @click="()=>{item.config.splice(key,1)}"></i>
                                                        </span>

                                            </b-col>
                                        </b-row>
                                        <b-row v-if="!item.config || item.config.length <= 0">
                                            <b-col sm="12">
                                                <b-alert  show variant="warning" class="mb-0">Вы не добавили еще ни одно поле для конигурации.</b-alert>
                                            </b-col>
                                        </b-row>

                                        <br>
                                    </b-col>
                                    <b-col sm="12">
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
    </div>
</template>


<script>
    import { gateways } from  '../../testData/data';

    export default {
        name: "GatewayForm",
        mounted() {
            this.getChannels();
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование";
                this.getItem(this.$route.params.id);
            }else {
                this.item = {
                    messenger_id: null,
                    name:"",
                    description:"",
                    link:"",
                    default:0,
                    config:[]
                };
                this.formTitle = "Новый шлюз";
            }
        },
        data: () => {
            return {
                messengers:[],
                formTitle: "Новый шлюз",
                item: {},
                errors:{}
            }
        },
        methods: {
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            addConfigField(){
              if(!this.item.config){
                  this.item.config = [];
              }
                this.item.config.push({field_name:"",placeholder:"",validation:"",message:""});
            },
            handleSave (evt) {
                evt.preventDefault();
                //this.errors = {};
                if(this.$route.params && this.$route.params.id === undefined) {
                    this.newItem();
                }
                else{
                    this.updateItem(this.$route.params.id)
                }
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            getItem(id) {
                axios.get(configs.apiUrl + "/gateways/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Gateway;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            newItem(){
                axios.post(configs.apiUrl + "/gateways", this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Gateway;
                        this.$router.push({ path: '/project-settings/gateways' });
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
                axios.put(configs.apiUrl + "/gateways/" + id, this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Gateway;
                        this.$router.push({ path: '/project-settings/gateways' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error Response = ",e.response);
                    this.errors = e.response.data.errors;
                });
            },
            getChannels(id) {
                axios.get(configs.apiUrl + "/messengers").then(response=>{
                    if(response.data.success === true) {
                        this.messengers = response.data.Messengers;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style scoped>
    .success-btn{
        margin-left: 10px;
    }
    .btn-add-field {
        cursor:pointer;
        position: absolute;
        padding-top: 5px;
        padding-left: 5px
    }
    .remove-col{
        padding-top: 9px;
    }
</style>