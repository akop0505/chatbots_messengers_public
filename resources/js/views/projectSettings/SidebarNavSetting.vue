<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="12">
                <b-card :header="caption">
                    <b-table :hover="hover" :striped="striped" :bordered="bordered" :small="small" :fixed="fixed" responsive="sm" :items="items" :fields="fields">
                        <template slot="path" slot-scope="data">
                            <span><b-link href="javascript:void(0)" v-text="data.item.path"></b-link></span>
                        </template>
                        <template slot="actions" slot-scope="data">
                            <i class="icon-pencil icons " @click="edit(data.item)"></i>
                        </template>
                        <template slot="roles" slot-scope="data">
                            <span v-for="role in data.item.roles" v-text="role.name" :key="'role_' + role.id" class="d-block"></span>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>


<script>

    import axios from 'axios';

    export default {
        name: "SidebarNavSetting",
        mounted(){
            this.getAll();
        },
        data: () => {
            return {

                caption: "<i class='fa fa-align-justify'></i> Боковая панель",
                hover:true,
                striped:true,
                bordered:true,
                small:true,
                fixed:true,
                items: [],
                fields: [
                    {key: 'label',       label:  'Название',       sortable: true},
                    {key: 'path',       label:  'Путь',       sortable: true},
                    //{key: 'name',       label:  'Имя пути',       sortable: true},
                    {key: 'roles',      label:  'Разрешения',   sortable: true},
                    {key: 'actions',    label:  '',             sortable: false, class:'action'}
                ]
            }
        },
        methods: {
            edit (value) {
                //this.item = _.clone(value, true);
                this.$router.push({ name: 'sidebarNavSetting.form', params: {id:value.id}});
            },
            getAll(){
                axios.get(configs.apiUrl + "/sidebar_navs").then((response)=>{
                    if(response.data.success === true){
                        this.items = response.data.SidebarNavs;
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            }
        }
    }
</script>

<style>
    .icons{
        margin-left: 10px;
        cursor: pointer;
    }
    .action {
        width: 70px !important;
    }
</style>