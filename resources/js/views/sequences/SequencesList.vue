<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-navbar type="light" >
                    <b-navbar-nav class="ml-auto">
                        <b-button variant="success" @click="newSequence()">
                            Новая последовательность
                        </b-button>
                    </b-navbar-nav>
                </b-navbar>
                <b-card :header="caption" v-if="items.length>0">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                            <i class="icon-trash icons " @click="toggleDeleteModal(data.item)"></i>
                        </template>
                        <template slot="by_default" slot-scope="data">
                            <i class="cui-check icons font-2xl d-block mt-1" v-if="data.item.by_default"></i>
                        </template>
                        <template slot="for_nonworking_time" slot-scope="data">
                            <i class="cui-check icons font-2xl d-block mt-1" v-if="data.item.for_nonworking_time"></i>
                        </template>
                        <template slot="project" slot-scope="data">
                            {{data.item.project.name}}
                        </template>
                    </b-table>
                    <nav>
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" prev-text="Пред" next-text="След" hide-goto-end-buttons @input="getAll(currentPage)"/>
                    </nav>
                </b-card>
                <b-card v-else>
                    <b-alert show variant="warning" class="mb-0">Вы не добавили еще ни одну последовательность, добавьте последовательность для начала работы.</b-alert>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modalDeleteSequences"
                 ref="modal"
                 title="Предупреждение"
                 @ok="remove"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="modalShow"
        >
            Вы действительно хотите удалить данную последовательность?
        </b-modal>
    </div>
</template>


<script>
    import { vSwitch, vCase, vDefault } from 'v-switch-case';

    export default {
        name: "Sequences",
        directives: {
            'switch': vSwitch,
            'case': vCase,
            'default': vDefault
        },
        mounted(){
            this.getAll();
        },
        filters: {
            fullName(value) {
                let name = "";
                if(!value){
                    return name;
                }
                name += value.last_name + " " +value.first_name;
                if(value.father_name){
                    name +=value.father_name;
                }
                return name;
            }
        },
        data: () => {
            return {
                modalShow: false,
                caption: "<i class='fa fa-align-justify'></i> Последовательности",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                item: {},
                items: [],
                fields: [
                    {key: 'name',                   label:  'Название ',                sortable: true},
                    {key: 'api_alias',              label:  'API Alias',                sortable: true},
                    {key: 'project',                label:  'Проект',                   sortable: true},
                    {key: 'by_default',             label:  'По умолчанию',             sortable: true},
                    {key: 'for_nonworking_time',    label:  'Для нерабочего времени',   sortable: true},
                    {key: 'actions',                label:  '',                         sortable: false, class:'action'},
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            edit (value) {
                this.item = _.clone(value, true);
                this.$router.push({ name: 'sequence.form',params: { id: value.id  }});
            },
            newSequence(){
                this.$router.push({ name: 'sequence.form' });
            },
            getAll(current) {
                if(current === undefined){
                    current = 1;
                }
                axios.get(configs.apiUrl + "/sequences?page=" + (current - 1) +"&limit=" + this.perPage).then((response)=>{
                    if(response.data.success === true) {
                        this.items = response.data.Sequence;
                        this.totalRows = response.data.totalRows
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            toggleDeleteModal(value) {
                if(this.modalShow){
                    this.item = {};
                }
                else if(value !== undefined){
                    this.item = _.clone(value, true);
                }
                this.modalShow = !this.modalShow;
            },
            remove(evt) {
                evt.preventDefault();
                axios.delete(configs.apiUrl + "/sequences/" + this.item.id).then((response)=>{
                    if(response.data.success){
                        this.toggleDeleteModal();
                        this.getAll(this.currentPage);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style >
    .messengers .fab{
        margin:2px;
    }
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>