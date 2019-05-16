<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newReceiver()">
                            Новый получатель
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length > 0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields" >
                        <template slot="project" slot-scope="data">
                            <span v-text="data.item.Project.name"></span>
                        </template>
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable}" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll()"/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одно получателя, добавьте получателя для начала работы.</b-alert>
                </b-card>
            </b-col>
        </b-row>

        <div>
            <b-modal id="modalPrevent"
                     ref="modal"
                     :title="item.id ? 'Изменить' : 'Новый'"
                     ok-title="Сохранить"
                     cancel-title="Отмена"
                     @ok="handleSave"
                     @hidden="clearItem">
                <form @submit.stop.prevent="handleSave">
                    <b-row>
                        <b-col sm="12">
                            <b-form-group>
                                <label for="loginInputId">Псевдоним</label>
                                <b-form-input type="text" id="loginInputId" placeholder="Псевдоним" v-model="item.alias"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <b-form-group>
                                <label for="nameInputId">Название</label>
                                <b-form-input type="text" id="nameInputId" placeholder="Название" v-model="item.name" ></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="12">
                            <b-form-group>
                                <label for="descriptionInputId">Описание</label>
                                <b-form-input type="text" id="descriptionInputId" placeholder="Описание" v-model="item.description" ></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </form>
            </b-modal>
        </div>
        <b-modal id="modalDeleteGateway"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данного получателя?
        </b-modal>
        <b-modal centered id="warning"
                 ref="modalWarning"
                 header-bg-variant="warning"
                 header-text-variant="dark"
                 title="Предупреждение"
                 ok-title="OK"
                 v-model="warningModalShow"
        >
            <b-container fluid>
                <span class="text-dark">{{ this.warningMessage}}</span>
            </b-container>
            <div slot="modal-footer" class="w-100">
                <b-btn class="float-right" variant="primary" @click="warningModalShow=!warningModalShow">
                    OK
                </b-btn>
            </div>
        </b-modal>
    </div>
</template>


<script>
    import { receivers } from  '../../testData/data';

    export default {
        name: "Receivers",
        mounted() {
            this.getAll();
        },
        data: () => {
            return {
                warningModalShow:false,
                warningMessage:'',
                caption: "<i class='fa fa-align-justify'></i> Получатели",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                fields: [
                    //{key: 'alias',      label:  'Псевдоним', sortable: true},
                    {key: 'name',       label:  'Название',    sortable: true},
                    {key: 'description',label:  'Описание',    sortable: true},
                    {key: 'project',    label:  'Проект',      sortable: true},
                    {key: 'actions',    label:  '',            sortable: false, class:'action'}
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
                modalShow:false
            }
        },
        methods: {
            clearItem () {
                this.item = {}
            },
            handleSave (evt) {
                evt.preventDefault();
                if(!this.item.id){
                    this.item.id = this.items.length;
                    this.item.registered = Date();
                    this.items.push(this.item);
                }
                this.$root.$emit('bv::hide::modal','modalPrevent');
            },
            edit (value) {
                this.item = _.clone(value, true);
                console.log(this.item);
                this.$router.push({ name: 'receiver.form', params:{ id:value.id }});
            },
            newReceiver (){
                this.$router.push({ name: 'receiver.form'});
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/receivers/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.getAll();
                        this.toggleDeleteModal();
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                    this.modalShow = !this.modalShow;
                }
                else if(value !== undefined){
                    if(!value.removable){
                        this.warningMessage = 'Данный получатель используется на одном из проектов.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            getAll(){
                axios.get(configs.apiUrl + "/receivers?page=" + (this.currentPage - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Receivers;
                        console.log(this.items);
                        this.totalRows = response.data.totalRows
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            }
        }
    }
</script>

<style >
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>