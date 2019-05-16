<template>
    <div v-if="value === null || value.actions.length === 0">
        <b-alert show variant="info">Задачи не назначены</b-alert>
    </div>
    <div v-else>
        <b-table :hover="hover" :striped="striped" :bordered="bordered" :fixed="fixed" responsive="sm" :items="value.actions" :fields="fields">
            <template slot="selected" slot-scope="data">
                <input type="checkbox" id="checkbox" v-model="data.item.selected">
            </template>
            <template slot="actions" slot-scope="data">
                <b-popover :target="'exPopover'+data.item.id"
                           triggers="click"
                           placement="auto"
                           ref="popover">
                    <template slot="title">
                        Вы действительно хотите удалить?
                    </template>
                    <div>
                        <b-btn @click="onClose(data.item.id)" size="sm" variant="danger">Cancel</b-btn>
                        <b-btn @click="onOk(data.item.id)" size="sm" variant="primary">Ok</b-btn>
                    </div>
                </b-popover>
                <i class="icon-trash icons " :id="'exPopover'+data.item.id"
                   @click="modalDeleteAction = true"></i>
            </template>
            <template slot="fio" slot-scope="data">
                {{data.item.first_name}} {{data.item.last_name}}
            </template>
            <template slot="owner" slot-scope="data">
                {{data.item.owner.last_name}} {{data.item.owner.first_name}}
            </template>
            <template slot="project" slot-scope="data">
                {{data.item.project.name}}
            </template>
            <template slot="status" slot-scope="data">
                {{getStatus(data.item.status)}}
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        name: "passage-edit",
        props: [
            'value',
        ],
        data: function () {
            return {
                popoverShow:false,
                caption: "<i class='fa fa-align-justify'></i> Лиды",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                fields: [
                    //{key: 'selected',       label:  '',             sortable: false, class: 'select'},
                    {key: 'type',           label:  'Тип',          sortable: true},
                    {key: 'start_at',       label:  'Начать в',     sortable: true},
                    {key: 'end_at',         label:  'Выполенена',   sortable: true},
                    {key: 'status',         label:  'Статус',       sortable: true},
                    {key: 'error',          label:  'Ошибка',       sortable: true},
                    {key: 'created_at',     label:  'Назначена',    sortable: true},
                    {key: 'actions',        label:  '',             sortable: false, class:'action'},
                ],
                currentPage: 1,
                perPage: 10,
                totalRows: 0,
            }
        },
        methods:{
            getStatus(status){
                switch (status){
                    case 'pending': return 'Ожидание';
                    case 'in_progress': return 'Выполняется';
                    case 'complete': return 'Готово';
                    case 'fail': return 'Ошибка';
                    default: return 'Нет статуса';
                }
            },
            onClose (id) {
                this.$root.$emit('bv::hide::popover', 'exPopover'+id);
            },
            onOk (id) {
                this.remove(id);
                this.$root.$emit('bv::hide::popover');
            },
            remove(id) {
                axios.delete(configs.apiUrl + "/passage-actions/" + id).then((response)=>{
                    if(response.data.success){
                        let newActions = this.value.actions.filter(function (el) {
                            return el.id !== id;
                        });
                        let newVal = Object.assign({}, this.value);
                        newVal.actions = newActions;
                        console.log(newVal);
                        this.$emit('input', newVal);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
        }
    }
</script>

<style scoped>

</style>