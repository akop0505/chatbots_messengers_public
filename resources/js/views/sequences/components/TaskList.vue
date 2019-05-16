<template>
    <div>
        <list-group>
            <slot />
        </list-group>

        <b-modal id="modalDeleteTask"
                 ref="modal"
                 title="Предупреждение"
                 @ok="destroy"
                 ok-title="Удалить"
                 cancel-title="Отмена"
                 v-model="showDeleteTaskModal"
        >
            Вы действительно хотите удалить данную задачу?
        </b-modal>
    </div>
</template>

<script>
    import { ContainerMixin } from 'vue-slicksort';
    import ListGroup from "bootstrap-vue/es/components/list-group/list-group";

    export default {
        components: {ListGroup},
        name:       "task-list",
        mixins:     [ContainerMixin],
        data: () => {
            return {
                showDeleteTaskModal: false,
                deleteTaskId: false,
            }
        },
        watch:{
            value: function (val) {
                //при изменении порядка обновляем индексы однотипных блоков
                let countByTypes = {};

                val.forEach((item, i, arr) => {

                    val[i].index = countByTypes[val[i].type] ? ++countByTypes[val[i].type] : countByTypes[val[i].type] = 1;

                });

                this.$emit('input', val);
            }
        },
        mounted() {
            this.$on('removeTask',(index) => {
                this.showDeleteTaskModal = true;
                this.deleteTaskId = index;
            });
        },
        methods:{
            destroy: function () {
                this.value.splice(this.deleteTaskId, 1);
                this.deleteTaskId = false;
            },
        }
    }
</script>

<style scoped>
</style>