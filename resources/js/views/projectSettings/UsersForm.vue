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
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="loginInputId">Логин</label>
                                            <b-form-input type="text" id="loginInputId" :state="validateState('username')" aria-describedby="usernameLiveFeedback" placeholder="Логин" v-model="item.username"></b-form-input>
                                            <b-form-invalid-feedback id="usernameLiveFeedback">
                                                <template v-if="validateState('username') === false " v-for="nErr in errors['username']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="passwordInputId">Пароль</label>
                                            <b-input-group>

                                                <b-form-input :type="passShow ? 'text': 'password'" id="passwordInputId" :state="validateState('password')" aria-describedby="passwordLiveFeedback" placeholder="" v-model="item.password" autocomplete="new-password"></b-form-input>
                                                <b-input-group-append class="messenger-input-group-prepend">
                                                        <i v-if="passShow" class="fa fa-eye fa-lg mt-2" @click="passShow=!passShow"></i>
                                                        <i v-if="!passShow" class="fa fa-eye-slash fa-lg mt-2" @click="passShow=!passShow"></i>
                                                </b-input-group-append>
                                                <b-form-invalid-feedback id="passwordLiveFeedback">
                                                        <template v-if="validateState('password') === false " v-for="nErr in errors['password']">
                                                            <span  v-text="nErr"></span><br>
                                                        </template>
                                                    </b-form-invalid-feedback>
                                            </b-input-group>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="emailInputId">Email</label>
                                            <b-form-input type="email" id="emailInputId" :state="validateState('email')" aria-describedby="emailLiveFeedback" placeholder="example@test.com" v-model="item.email" ></b-form-input>
                                            <b-form-invalid-feedback id="emailLiveFeedback">
                                                <template v-if="validateState('email') === false " v-for="nErr in errors['email']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="first_nameInputId">Имя</label>
                                            <b-form-input type="text" id="first_nameInputId" :state="validateState('first_name')" aria-describedby="firstNameLiveFeedback" placeholder="Имя" v-model="item.first_name" ></b-form-input>
                                            <b-form-invalid-feedback id="firstNameLiveFeedback">
                                                <template v-if="validateState('first_name') === false " v-for="nErr in errors['first_name']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="phoneInputId">Телефон</label>
                                            <input :class="{'is-invalid':validateState('phone') === false,'form-control':true}" :maxlength="13" @focus="setPhonePlus()" type="text" id="phoneInputId" :state="validateState('phone')" aria-describedby="phoneLiveFeedback" placeholder="+79099876543" v-model="item.phone" />
                                            <b-form-invalid-feedback id="phoneLiveFeedback">
                                                <template v-if="validateState('phone') === false " v-for="nErr in errors['phone']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="lastNameInputId">Фамилия</label>
                                            <b-form-input type="text" id="lastNameInputId" :state="validateState('last_name')" aria-describedby="lastNameLiveFeedback" placeholder="Фамилия" v-model="item.last_name" ></b-form-input>
                                            <b-form-invalid-feedback id="lastNameLiveFeedback">
                                                <template v-if="validateState('last_name') === false " v-for="nErr in errors['last_name']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6" v-if="$user.get().role === 'admin'">
                                        <b-form-group>
                                            <label for="roleInputId">Роль</label>
                                            <b-form-select :state="validateState('role_id')" aria-describedby="role_idLiveFeedback"  v-model="item.role_id" class="mb-3" id="roleInputId">
                                                <option :value="null" selected>Выберите роль</option>
                                                <option v-for="role in roles" :value="role.id" v-text="role.name"></option>
                                            </b-form-select>
                                            <b-form-invalid-feedback id="role_idLiveFeedback">
                                                <template v-if="validateState('role_id') === false " v-for="nErr in errors['role_id']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6">
                                        <b-form-group>
                                            <label for="fatherNameInputId">Отчество</label>
                                            <b-form-input type="text" id="fatherNameInputId" :state="validateState('father_name')" aria-describedby="fatherNameLiveFeedback" placeholder="Отчество" v-model="item.father_name" ></b-form-input>
                                            <b-form-invalid-feedback id="fatherNameLiveFeedback">
                                                <template v-if="validateState('father_name') === false " v-for="nErr in errors['father_name']">
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="6" v-if="$user.get().role === 'admin'">
                                        <b-form-group :state="validateState('client_id')" aria-describedby="parent_idLiveFeedback">
                                            <label for="parent_idInputId">Клиент (родитель)</label>
                                            <template>
                                                <!-- object value -->
                                                <model-list-select
                                                        :class="{'form-control':true, 'is-invalid': validateState('parent_id') === false,'search-select-invalid':validateState('parent_id') === false}"
                                                        :list="clients"
                                                        option-value="id"
                                                        :custom-text="selectFullName"
                                                        v-model="item.parent_id"
                                                        placeholder="Выберите клиента">
                                                </model-list-select>
                                            </template>
                                            <b-form-invalid-feedback id="parent_idLiveFeedback" >
                                                <template v-if="validateState('parent_id') === false " v-for="nErr in errors.parent_id" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <div class="d-flex">
                                            <b-button class="btn btn-danger ml-auto" @click="handleCancel">Отмена</b-button>
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
    import { users }from  '../../testData/data';
    import { ModelListSelect } from 'vue-search-select';


    export default {
        name: "UsersForm",
        components: {
            ModelListSelect
        },
        mounted(){
            if(this.$user.get().role === 'admin'){
                this.loadRoles();
                this.loadClients();
            }


            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование пользователя";
                this.getItem(this.$route.params.id);
            }else {
                this.formTitle = "Новый пользователь";
                this.item = {
                    login:'',
                    password:'',
                    first_name:'',
                    last_name:'',
                    father_name:'',
                    phone: '',
                    email:'',
                    role_id: null,
                    parent_id:null
                }
            }
        },
        data: () => {
            return {
                formTitle: "Новый пользователь",
                item: {},
                roles: [],
                clients:[],
                errors: [],
                passShow:false
            }
        },
        methods: {
            setPhonePlus(){
                if(!this.item.phone){
                    this.item.phone = "+";
                }
            },
            selectFullName (v) {
                return `${v.last_name} ${v.first_name} ${v.father_name || ''}`
            },
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            loadRoles(){
                axios.get(configs.apiUrl + "/roles?offset=0&limit=1000").then(response=>{
                    if(response.data.success == true) {
                        this.roles = response.data.Roles;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    console.error(e);
                });
            },
            loadClients(){
                axios.get(configs.apiUrl + "/select/clients").then(response=>{
                    if(response.data.success === true) {
                        this.clients = response.data.Clients;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            getItem(id) {
                axios.get(configs.apiUrl + "/users/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.User;
                        this.$set(this.item,'password',null);
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
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
                axios.post(configs.apiUrl + "/users", this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.User;
                        this.$router.push({ name: 'user' });
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
                axios.put(configs.apiUrl + "/users/" + id, this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.User;
                        this.$router.push({ name: 'user' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    this.errors = e.response.data.errors;
                });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            }
        }
    }
</script>

<style scoped>
    .success-btn{
        margin-left: 10px;
    }
    .messenger-input-group-prepend{
        width: 50px;
        /*padding-top:5px;*/
        padding-left:5px;
    }
    .search-select-invalid{
        border-color: #f86c6b !important;
    }
</style>