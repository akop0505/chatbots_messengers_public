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
                                            <label for="nameInputId">Название</label>
                                            <b-form-input :state="(errors.name !== undefined && errors.name.length) ? false : null"  type="text" id="nameInputId" placeholder="Название" v-model="item.name" aria-describedby="nameLiveFeedback"></b-form-input>
                                            <b-form-invalid-feedback id="nameLiveFeedback" >
                                                <template v-if="errors.name && errors.name.length" v-for="nErr in errors.name" >
                                                    <span  v-text="nErr"></span><br>
                                                </template>
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-form-group>
                                            <label for="descriptionInputId">Описание</label>
                                            <b-form-input :state=" (errors.description !== undefined && errors.description.length) ? false : null" type="text" id="descriptionInputId" placeholder="Описание" v-model="item.description" aria-describedby="descriptionLiveFeedback"></b-form-input>
                                            <b-form-invalid-feedback id="descriptionLiveFeedback" >
                                                <template  v-if="errors.description && errors.description.length" v-for="dErr in errors.description" >
                                                    <span  v-text="dErr"></span><br>
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
    import { roles } from  '../../testData/data';
    import axios from 'axios';

    export default {
        name: "RolesForm",
        mounted() {
            if(this.$route.params && this.$route.params.id !== undefined) {
                this.formTitle = "Редактирование";
                this.action = "edit";
                this.getItem(this.$route.params.id);
            }else {
                this.getItem();
                this.action = "create";
                this.formTitle = "Новая роль";
            }
        },
        data: () => {
            return {
                action:"create",
                formTitle: "Новая роль",
                item: {},
                errors:{}
            }
        },
        methods: {
            handleSave (evt) {
                evt.preventDefault();
                if(this.action === "edit"){
                    this.updateItem(this.$route.params.id);
                }else{
                    this.newItem();
                }
                //this.$router.push({ path: '/project-settings/roles' });
            },
            handleCancel (evt) {
                evt.preventDefault();
                this.$router.go(-1);
            },
            getItem(id) {
                if(!id){
                    this.item =  {
                        name:"",
                        description:""
                    }
                }else{
                    axios.get(configs.apiUrl + "/roles/" + id).then(response=>{
                        if(response.data.success === true) {
                            this.item = response.data.Role;
                        }
                        else{
                            //console.error(response.data);
                        }
                    }).catch(e=>{
                        //console.error(e);
                    });
                }

            },
            newItem(){
                axios.post(configs.apiUrl + "/roles", this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Role;
                        this.$router.push({ path: '/project-settings/roles' })
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error = ",e);
                    //console.log("error Response = ",e.response);
                    this.errors = e.response.data.errors;
                });
            },
            updateItem(id){
                axios.put(configs.apiUrl + "/roles/" + id, this.item).then(response=>{
                    if(response.data.success === true) {
                        this.item = response.data.Role;
                        this.$router.push({ path: '/project-settings/roles' })
                    }
                    else{
                        //console.error(response.data);
                    }
                }).catch(e=>{
                    //console.log("error = ",e);
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
</style>