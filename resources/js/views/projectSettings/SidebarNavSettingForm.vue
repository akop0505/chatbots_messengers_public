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
                                        <h5><b>Название</b> : <span v-text="item.label"></span></h5>
                                    </b-col>
                                    <b-col sm="12">
                                        <h5><b>Путь</b> : <span><b-link href="javascript:void(0)" v-text="item.path"></b-link></span></h5>
                                    </b-col>
                                    <!--<b-col sm="12">
                                        <h5><b>Имя пути</b> : <span v-text="item.name"></span></h5>
                                    </b-col>-->
                                    <b-col sm="12">
                                        <hr>
                                        <h3>Разрешения <span @click="toggleNewRole()" ><i class="nav-icon icon-plus btn-new-role"></i></span></h3>
                                        <b-row class="keys_block">
                                            <b-col sm="1">
                                                <b>#</b>
                                            </b-col>
                                            <b-col sm="3">
                                                <b>Название</b>
                                            </b-col>
                                            <b-col sm="3">
                                                <b>Описание</b>
                                            </b-col>
                                            <b-col sm="1">
                                                <b>Удалить</b>
                                            </b-col>
                                        </b-row>
                                        <template v-for="(role,rKey) in item.roles">
                                            <b-row>
                                                <b-col sm="1">
                                                    {{ rKey + 1 }}
                                                </b-col>
                                                <b-col sm="3">
                                                    {{ role.name}}
                                                </b-col>
                                                <b-col sm="3">
                                                    {{ role.description}}
                                                </b-col>
                                                <b-col sm="1">
                                                    <span @click="removeRole(rKey)" class=""><i class="nav-icon icon-close"></i></span>
                                                </b-col>
                                            </b-row>
                                        </template>
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
        <b-modal id="modalNewRole"
                 ref="modal"
                 title="Добавить роль"
                 @ok="handleOk"
                 ok-title="Добавить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            <b-form-select  class="mb-3" :id="'rolesSelectId'" v-model="selectedRole">
                <option :value="null" selected>Выберите роль</option>
                <option v-for="role in roles" :value="role" v-text="role.name"></option>
            </b-form-select>
        </b-modal>
    </div>
</template>


<script>
    export default {
        name: "SidebarNavSettingForm",
        mounted() {
            this.loadRoles();
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование";
                this.getItem(this.$route.params.id);
            }
        },
        data: () => {
            return {
                modalShow:false,
                selectedRole: null,
                formTitle: "",
                item: {},
                roles: [],
                errors:{}
            }
        },
        methods: {
            toggleNewRole(){
                this.selectedRole = null;
                this.modalShow = !this.modalShow;
            },
            removeRole(index){
                this.item.roles.splice(index,1);
            },
            handleOk(){
                let existedRole = this.item.roles.find(val=>{
                    return val.id === this.selectedRole.id;
                });
                if(!existedRole){
                    this.item.roles.push(this.selectedRole);
                }
            },
            handleSave (evt) {
                evt.preventDefault();
                //this.errors = {};
                if(this.$route.params && this.$route.params.id === undefined) {
                    //this.newItem();
                }
                else{
                    this.updateItem(this.$route.params.id)
                }
                //this.$router.push({ path: '/project-settings/gateways' });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            loadRoles(){
                axios.get(configs.apiUrl + "/roles?offset=0&limit=1000").then(response=>{
                    if(response.data.success === true) {
                        this.roles = response.data.Roles;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            getItem(id) {
                axios.get(configs.apiUrl + "/sidebar_navs/" + id).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.SidebarNav;
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.error(e);
                });
            },
            updateItem(id){
                let roles = this.item.roles.map(val=>{
                    return val.id;
                });

                axios.put(configs.apiUrl + "/sidebar_navs/" + id, {"Roles": roles } ).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.SidebarNav;
                        this.$router.push({ name: 'sidebarNavSetting' });
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error Response = ",e.response);
                    this.errors = e.response.data.errors;
                });
            }
        }
    }
</script>

<style scoped>
    .success-btn{
        margin-left: 10px;
    }
    .btn-new-role {
        cursor:pointer;
        position: absolute;
        padding-top: 5px;
        padding-left: 5px
    }
</style>