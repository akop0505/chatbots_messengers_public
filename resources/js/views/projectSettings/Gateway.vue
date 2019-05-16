<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newGateway()">
                            Новый шлюз
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i :class="{'icon-trash icons':true, 'text-danger':!data.item.removable }" @click="toggleDeleteModal(data.item)"></i>
                        </template>
                        <template slot="by_default" slot-scope="data">
                            <i class="cui-check icons font-2xl d-block mt-1" v-if="data.item.by_default"></i>
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)" />
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одно шлюза, добавьте шлюз для начала работы.</b-alert>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteGateway"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данный шлюз?
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

    import { gateways } from  '../../testData/data';
    import axios from 'axios';

    export default {
        name: "Gateway",
        mounted(){
            this.getAll();
        },
        data: () => {
            return {
                modalShow: false,
                warningModalShow: false,
                warningMessage : '',
                caption: "<i class='fa fa-align-justify'></i> Шлюзы",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: gateways,
                fields: [
                    {key: 'name',       label:  'Название шлюза',       sortable: true},
                    {key: 'description',label:  'Описание шлюза',       sortable: true},
                    {key: 'link',       label:  'Ссылка на офф. сайт',   sortable: true},
                    {key: 'by_default', label:  'По умолчанию',         sortable: true},
                    // {key: 'created_at', label:  'Создан',               sortable: true},
                    {key: 'actions',    label:  '',             sortable: false, class:'action'}
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            getRowCount (items) {
                return items.length
            },
            edit (value) {
                //this.item = _.clone(value, true);
                this.$router.push({ path: '/project-settings/gateways/form/'  + value.id });
            },
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                    this.modalShow = !this.modalShow;
                }
                else if(value !== undefined){
                    if(!value.removable){
                        this.warningMessage = 'Данный шлюз используется на одном из проектов.';
                        this.warningModalShow = !this.warningModalShow;
                    }else{
                        this.item = _.clone(value, true);
                        this.modalShow = !this.modalShow;
                    }
                }
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/gateways/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.toggleDeleteModal();
                        this.getAll(this.currentPage);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            newGateway () {
                this.$router.push({ path: '/project-settings/gateways/form' });
            },
            getAll(current){
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/gateways?page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true){
                        this.items = response.data.Gateways;
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