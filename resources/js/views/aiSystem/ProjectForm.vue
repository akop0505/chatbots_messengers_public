<template>
    <div class="animated fadeIn">
        <b-card >
            <div v-if="newCreated">
                <b-alert :show="5" variant="success" >Новый проект успешно создан.</b-alert>
            </div>

            <!--<b-collapse id="collapse1" visible>-->
                <b-card-body>
                    <form @submit.stop.prevent="handleSave">
                        <b-row>
                            <b-col sm="12">
                                <b-row>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="nameInputId">Название проекта</label>
                                            <b-form-input :state="validateState('name')" aria-describedby="nameLiveFeedback" type="text" id="nameInputId" placeholder="Новый проект" v-model="item.name" ></b-form-input>
                                            <b-form-invalid-feedback id="nameLiveFeedback" >
                                                <template v-if="validateState('name') === false" v-for="nErr in errors.name" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="web_siteInputId">Web сайт</label>
                                            <b-form-input :state="validateState('web_site')" aria-describedby="web_siteLiveFeedback" type="text" id="web_siteInputId" placeholder="http://www.example.com" v-model="item.web_site"></b-form-input>
                                            <b-form-invalid-feedback id="web_siteLiveFeedback" >
                                                <template v-if="validateState('web_site') === false " v-for="nErr in errors.web_site">
                                                    <span v-if="typeof nErr === 'object'" v-for="n in nErr" >{{n}}<br></span>
                                                    <span v-if="typeof nErr === 'string'" v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12" v-if="$user.get().role !== 'client'">
                                        <b-form-group :state="validateState('client_id')" aria-describedby="client_idLiveFeedback">
                                            <label for="clientSelectId">Клиент</label>
                                            <template>
                                                <!-- object value -->
                                                <model-list-select
                                                        :class="{'form-control':true, 'is-invalid': validateState('client_id') === false,'search-select-invalid':validateState('client_id') === false}"
                                                        :list="clients"
                                                        option-value="id"
                                                        :custom-text="selectFullName"
                                                        v-model="item.client_id"
                                                        placeholder="Выберите клиента">
                                                </model-list-select>
                                            </template>
                                            <b-form-invalid-feedback id="client_idLiveFeedback" >
                                                <template v-if="validateState('client_id') === false " v-for="nErr in errors.client_id" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </b-col>
                            <b-col sm="12">
                                <b-row>
                                    <b-col sm="12">
                                        <h3>Каналы <span @click="toggleModal()" class="btn-new-messenger"><i class="nav-icon icon-plus"></i></span></h3>
                                        <b-alert v-if="item.ProjectMessengers === undefined || item.ProjectMessengers.length <= 0 " show variant="warning">К проекту не добавлено ни одного канала.</b-alert>
                                        <hr>
                                    </b-col>
                                </b-row>
                            </b-col>
                            <b-col sm="12">
                                <b-row v-for="(val,key) in item.ProjectMessengers" :key="'pm'+key">
                                    <!--Gateway selected -->
                                    <template  v-if="val.gateway_id != null">
                                        <b-col sm="2" class="custom-icon">
                                            <channel-thumbnail :channel="val.gateway.messenger.alias">{{val.gateway.name}}</channel-thumbnail>
                                        </b-col>
                                        <b-col  v-for="(setting,configKey) in val.GatewaySettings" :key="'setting_' + key + configKey">
                                            <b-form-group>
                                                <label v-text="setting.placeholder || setting.field_name"></label>
                                                <b-form-input :state="validateState('ProjectMessengers.'+ key +'.GatewaySettings.'+configKey + '.field_value')" :aria-describedby="setting.field_name + 'LiveFeedback'+key +'_'+configKey" type="text" placeholder="" v-model="setting.field_value"></b-form-input>
                                                <b-form-invalid-feedback :id="setting.field_name + 'LiveFeedback'+key +'_'+configKey">
                                                    <template v-if="validateState('ProjectMessengers.'+ key +'.GatewaySettings.'+configKey + '.field_value') === false " v-for="nErr in errors['ProjectMessengers.'+ key +'.GatewaySettings.'+configKey + '.field_value']">
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-form-group>
                                        </b-col>
                                        <b-col sm="2" class="ml-auto">
                                            <b-form-group>
                                                <label :for="'permissionSelectId' + key ">Разрешение</label>
                                                <b-form-select :state="validateState('ProjectMessengers.'+ key +'.permission_id')" :aria-describedby="'permission_idLiveFeedback'+key" v-model="val.permission_id" class="mb-3" :id="'permissionSelectId' + key ">
                                                    <option :value="null" selected>Выберите разрешение</option>
                                                    <option v-for="permission in permissions" :value="permission.id" v-text="permission.name"></option>
                                                </b-form-select>
                                                <b-form-invalid-feedback :id="'permission_idLiveFeedback'+key" >
                                                    <template v-if="validateState('ProjectMessengers.'+ key +'.permission_id') === false " v-for="nErr in errors['ProjectMessengers.'+ key +'.permission_id']">
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-form-group>
                                        </b-col>
                                        <b-col sm="1">
                                            <span @click="removeProjectMessenger(key)" class="btn-remove-messenger"><i class="nav-icon icon-close"></i></span>
                                        </b-col>

                                        <div class="w-100"></div>

                                        <b-col sm="3">
                                            <b-form-group>
                                                <label :for="'subscribeAction' + key ">Действие при подписке</label>
                                                <b-form-select :state="validateState('ProjectMessengers.'+ key +'.subscribe.action')" :aria-describedby="'subscribeActionLiveFeedback'+key" v-model="val.subscribe.action" class="mb-3" :id="'subscribeAction' + key ">
                                                    <option :value="null" selected>Ничего</option>
                                                    <option value="template">По шаблону</option>
                                                </b-form-select>
                                                <b-form-invalid-feedback :id="'subscribeLiveFeedback'+key" >
                                                    <template v-if="validateState('ProjectMessengers.'+ key +'.subscribe.action') === false " v-for="nErr in errors['ProjectMessengers.'+ key +'.subscribe.action']">
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-form-group>
                                        </b-col>
                                        <b-col sm="3" v-if="val.subscribe.action">
                                            <b-form-group :state="validateState('ProjectMessengers.'+ key +'.subscribe.template_id')"
                                                          :aria-describedby="'subscribeTemplateIdLiveFeedback'+key">
                                                <label>Шаблон сообщения</label>
                                                <model-list-select
                                                        :class="{'form-control':true, 'is-invalid': validateState('ProjectMessengers.'+ key +'.subscribe.template_id') === false,'search-select-invalid':validateState('ProjectMessengers.'+ key +'.subscribe.template_id') === false}"
                                                        :list="templates"
                                                        v-model="val.subscribe.template_id"
                                                        option-value="id"
                                                        option-text="name"
                                                        :selected-option="val.subscribe.template_id"
                                                        placeholder="Выберите шаблон">
                                                </model-list-select>
                                                <b-form-invalid-feedback :id="'subscribeTemplateIdLiveFeedback'+key" >
                                                    <template v-if="validateState('ProjectMessengers.'+ key +'.subscribe.template_id') === false " v-for="nErr in errors['ProjectMessengers.'+ key +'.subscribe.template_id']" >
                                                        <span  v-text="nErr"></span><br>
                                                    </template>
                                                </b-form-invalid-feedback>
                                            </b-form-group>

                                            <!--<b-form-group>-->

                                                <!--<label class="d-flex align-items-center justify-content-between w-100">-->
                                                    <!--Callback <i v-b-tooltip.hover title="На указаный URL приходит запрос с введеным кодом или телефоном, в ответе ожидается подтвержение авторизации клиента" class="icon-question icons pull-right"></i>-->
                                                <!--</label>-->
                                                <!--<b-form-input :state="validateState('ProjectMessengers.'+ key +'.subscribe.callback')" :aria-describedby="'subscribeCallbackLiveFeedback'+key" type="text" placeholder="" v-model="val.subscribe.callback"></b-form-input>-->
                                                <!--<b-form-invalid-feedback :id="'subscribeCallbackLiveFeedback'+key">-->
                                                    <!--<template v-if="validateState('ProjectMessengers.'+ key +'.subscribe.callback') === false " v-for="nErr in errors['ProjectMessengers.'+ key +'.subscribe.callback']">-->
                                                        <!--<span  v-text="nErr"></span><br>-->
                                                    <!--</template>-->
                                                <!--</b-form-invalid-feedback>-->
                                            <!--</b-form-group>-->
                                        </b-col>
                                    </template>
                                </b-row>
                            </b-col>
                            <b-col sm="12">
                                <b-row>
                                    <b-col sm="12">
                                        <h3>Ключи доступа <span v-if="action == 'update'" @click="createNewKey()" class="btn-new-messenger"><i class="nav-icon icon-plus"></i></span></h3>
                                        <b-alert v-show="errors.access_key_limit" show variant="danger">{{ errors.access_key_limit }}</b-alert>
                                        <hr>
                                    </b-col>
                                    <b-col sm="12" v-if="action == 'update' && (item.ProjectKeys === undefined || item.ProjectKeys.length<=0)">
                                        <b-alert show variant="warning">К проекту не добавлено ни одного ключа.</b-alert>
                                    </b-col>
                                    <b-col sm="12" v-if="action == 'create'">
                                        <b-alert show variant="warning">Добавление ключей доступа к проекту возможно только в режиме редактирования.</b-alert>
                                    </b-col>
                                    <b-col sm="12" v-if="action == 'update' && (item.ProjectKeys !== undefined && item.ProjectKeys.length > 0)">
                                        <b-row class="keys_block">
                                            <b-col sm="1">
                                                <b>#</b>
                                            </b-col>
                                            <b-col sm="3">
                                                <b>Ключи доступа</b>
                                            </b-col>
                                            <b-col sm="2">
                                                <b>Создатель</b>
                                            </b-col>
                                            <b-col sm="2">
                                                <b>Дата генерации</b>
                                            </b-col>
                                            <b-col sm="2">
                                                <b>Доступность</b>
                                            </b-col>
                                            <b-col sm="1">
                                                <b>Удалить</b>
                                            </b-col>
                                        </b-row>
                                        <template v-for="(pk,pkKey) in item.ProjectKeys">
                                            <b-row class="keys_block">
                                                <b-col sm="1">
                                                    <b v-text="pkKey+1"></b>
                                                </b-col>
                                                <b-col sm="3">
                                                    <b v-text="pk.access_key"></b>
                                                </b-col>
                                                <b-col sm="2">
                                                    <b>{{ pk.user | userFullName }}</b>
                                                </b-col>
                                                <b-col sm="2">
                                                    <b>{{ pk.created_at}}</b>
                                                </b-col>
                                                <b-col sm="2">
                                                    <b-form-checkbox-group stacked id="inactiveInputId">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" :id="'customChk1' + pkKey" v-model="pk.inactive" v-on:change="updateInactiveStatus(pkKey)">
                                                            <label class="custom-control-label" :for="'customChk1' + pkKey">закрыть доступ</label>
                                                        </div>
                                                    </b-form-checkbox-group>
                                                </b-col>
                                                <b-col sm="1">
                                                    <span @click="toggleRemoveKey(pk)" class=""><i class="nav-icon icon-close"></i></span>
                                                </b-col>
                                            </b-row>
                                        </template>
                                    </b-col>
                                </b-row>
                            </b-col>
                            <b-col sm="12">
                                <div class="d-flex">
                                    <b-button class="btn btn-danger ml-auto" @click="handleCancel">Отмена</b-button>
                                    <b-button class="btn btn-success success-btn"   @click="handleSave">Сохранить</b-button>
                                </div>
                            </b-col>
                        </b-row>
                    </form>
                </b-card-body>
            <!--</b-collapse>-->
        </b-card>
        <select-gateway-modal ref="gateway_modal"></select-gateway-modal>
        <b-modal id="modalRemoveKey"
                 ref="modal"
                 title="Предупреждение"
                 @ok="handleRemoveKey"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalRemoveKeyShow"
        >
            Вы действительно хотите удалить данный ключ?
        </b-modal>
    </div>
</template>


<script>
    import axios from 'axios';
    import { ModelListSelect } from 'vue-search-select';
    import SelectGatewayModal from './ModalComponents/SelectGatewayModal';
    import ChannelThumbnail from './../../components/ChannelThumbnail';

    export default {
        name: "ProjectsForm",
        components: {
            ModelListSelect,
            SelectGatewayModal,
            ChannelThumbnail
        },
        filters: {
            userFullName: function(value){
                if(value){
                    let fullName = "";
                    fullName += value.first_name ? value.first_name  + " " : "";
                    fullName += value.last_name ? value.last_name  + " " : "";
                    fullName += value.father_name ? value.father_name: "";
                    return fullName;

                }else{
                    return "";
                }
            }
        },
        updated(){
            if(this.$route.params && this.$route.params.newCreated){
                this.newCreated = true;
                delete this.$route.params.newCreated;
                this.$router.replace({ params :this.$route.params });
            }
        },
        mounted() {
            this.getPermissions();
            this.$on("select-gateway",(gateway) => {
                this.selectGateway(gateway);
            });

            if(this.$user.get().client !== 'client'){
                this.getClients();
            }

            if(this.$route.params && this.$route.params.id !== undefined) {
                this.loadTemplates(this.$route.params.id);
                this.formTitle = "Редактирование";
                this.getProject(this.$route.params.id);
                this.action = "update";
            }else {
                this.formTitle = "Новый проект";
                this.action = "create";
                this.item = {
                    name: "",
                    web_site: "",
                    client_id: null,
                    ProjectMessengers: [],
                    ProjectKeys:[]
                }
            }
        },
        data: () => {
            return {
                newCreated:false,
                modalRemoveKeyShow:false,
                action:"update",
                errors:{},
                messengers: [],
                permissions: [],
                formTitle: "Новый проект",
                item: {},
                gateways:[],
                modalShow: false,
                selectedMessenger:null,
                clients:[],
                templates:[],
            }
        },
        watch: {
            action : function(newVal,oldVal){
                if(newVal !== oldVal){
                    if(this.$route.params && this.$route.params.id !== undefined){
                        this.getProject(this.$route.params.id);
                    }
                }
            }
        },
        computed:{
          stopLoader() {
              return true;
          }
        },
        methods: {
            loadTemplates(id) {
                axios.get(configs.apiUrl + "/select/templates/?project_id="+id).then(response=>{
                    if(response.data.success === true){
                        this.templates = response.data.Templates;
                    }else{
                        this.templates = {};
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            selectGateway(gateway){
                let newChannel = {
                    permission_id: null,
                    subscribe: {
                        action: null,
                        callback: null,
                        template_id: null,
                    },
                    gateway:gateway,
                    gateway_id: gateway.id,
                    GatewaySettings:[]
                };
                if(gateway.config.length){
                    for(let config of gateway.config){
                        newChannel.GatewaySettings.push({
                            field_name: config.field_name,
                            field_value: "",
                            placeholder: config.placeholder
                        })
                    }
                }
                this.item.ProjectMessengers.push(newChannel);
                console.log(this.item);
            },
            selectFullName (v) {
                return `${v.last_name} ${v.first_name} ${v.father_name || ''}`
            },
            toggleModal() {
                this.$refs.gateway_modal.toggle();
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
                    this.updateProject(this.item.id);
                }else{
                    this.newProject();
                }
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            getPermissions() {
                axios.get(configs.apiUrl + "/permissions").then(response=>{
                    if(response.data.success === true){
                        this.permissions = response.data.Permissions;
                    }
                    else{
                        this.permissions = {};
                    }
                }).catch(e => {
                    console.log(e);
                })
            },
            getProject(id){
                axios.get(configs.apiUrl + "/projects/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Project;
                        this.item.ProjectMessengers.forEach((item, i) => {
                            if (!item.subscribe) {
                                Vue.set(item, 'subscribe', {
                                    action:   null,
                                    callback: null,
                                    template_id: null,
                                });
                            }
                        });
                        for(let pm of  this.item.ProjectMessengers){
                            for(let setting of pm.gateway.config){

                                let config = pm.GatewaySettings.find((val)=>{
                                     if(val.field_name == setting.field_name){
                                         val.placeholder = setting.placeholder;
                                         return true;
                                    }
                                });
                                if(!config){
                                    pm.GatewaySettings.push({
                                        placeholder:setting.placeholder,
                                        field_name:setting.field_name,
                                        field_value:''
                                    });
                                    console.log(pm.GatewaySettings);
                                }
                            }
                        }
                    }
                    else {
                        this.item = {};
                    }
                }).catch(e => {
                    //console.error(e);
                })
            },
            getClients(){
                axios.get(configs.apiUrl + "/select/clients").then(response=>{
                    if(response.data.success === true) {
                        this.clients = response.data.Clients;
                    }
                    else {
                        this.item = {};
                    }
                }).catch(e => {
                    //console.error(e);
                })
            },
            removeProjectMessenger(key){
                if(key >= 0){
                    this.item.ProjectMessengers.splice(key,1);
                }
            },
            removeProjectKeys(key){
                if(key >= 0){
                    if(this.item.ProjectKeysToRemove === undefined){
                        this.item.ProjectKeysToRemove = [];
                    }
                    this.item.ProjectKeysToRemove.push(this.item.ProjectKeys[key].id);
                    this.item.ProjectKeys.splice(key,1);
                }
            },
            newProject(){
                let dataToSave = Object.assign({},this.item);
                for(let val in dataToSave.ProjectMessengers){
                    delete(val.messengers);
                }
                axios.post(configs.apiUrl + "/projects",dataToSave).then(response=>{
                    console.log(response.data);
                    if(response.data.success === true){
                        let id = response.data.Project.id;
                        this.errors = [];
                        this.$set(this.item,'id',id);
                        this.$router.push({ name: 'project.form',params:{id:this.item.id,newCreated:true} });
                        this.action = "update";
                        this.formTitle = "Редактирование";
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
            updateProject(id){
                let dataToSave = Object.assign({},this.item);
                for(let val in dataToSave.ProjectMessengers){
                    delete(val.messengers);
                }
                delete(dataToSave.ProjectKeys);
                delete(dataToSave.ProjectKeys);
                axios.put(configs.apiUrl + "/projects/" + id,dataToSave).then(response=>{
                    if(response.data.success === true){
                        this.item = response.data.Project;
                        this.item.ProjectMessengers = response.data.Project.ProjectMessengers;
                        this.$router.push({ name: 'project' });
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
            createNewKey(){
                axios.post(configs.apiUrl + "/projects/keys/" + this.$route.params.id,{}).then(response=>{
                    if(response.data.success === true){
                        if(this.item.ProjectKeys === undefined){
                            this.item.ProjectKeys = [];
                        }
                        this.item.ProjectKeys.push(response.data.ProjectKey);
                    }else{
                    }
                }).catch(e => {
                    if(e.response.data.success === false){
                        this.errors = e.response.data.errors
                    }
                })
            },
            updateInactiveStatus(pkKey) {
                let inactive = this.item.ProjectKeys[pkKey].inactive;
                axios.put(configs.apiUrl + "/projects/keys/" + this.item.ProjectKeys[pkKey].id,{inactive}).then(response=>{
                    if(response.data.success !== true) {
                        this.item.ProjectKeys[pkKey].inactive  = !inactive;
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            toggleRemoveKey(value) {
                if(this.modalRemoveKeyShow){
                    this.keyToRemove = {};
                }
                else if(value !== undefined){
                    this.keyToRemove = _.clone(value, true);
                }
                this.modalRemoveKeyShow = !this.modalRemoveKeyShow;
            },
            handleRemoveKey(){
                axios.delete(configs.apiUrl + "/projects/keys/" + this.keyToRemove.id).then(response=>{
                    if(response.data.success === true) {
                        this.item.ProjectKeys = this.item.ProjectKeys.filter(value =>{
                            return value.id !== this.keyToRemove.id;
                        });
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        }
    }
</script>

<style scoped>
    .success-btn {
        margin-left: 10px;
    }
    .messenger-input-group-prepend{
        display: flex;
        width: 100%;
        align-items: center;
        padding-top:3px;
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
    }
</style>