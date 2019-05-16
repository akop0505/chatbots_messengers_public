<template>
    <b-card
            header-tag="header"
            footer-tag="footer">
        <div>
            <b-nav justified tabs>
                <b-nav-item :active="tab == 'settings'" @click="tab = 'settings'">Настройка проекта</b-nav-item>
                <b-nav-item :active="tab == 'variables'" @click="tab = 'variables'">Переменные проекта</b-nav-item>
                <b-nav-item :active="tab == 'additional'" @click="tab = 'additional'">Дополнительная конфигурация</b-nav-item>
            </b-nav>
        </div>
        <template v-if="tab=='additional'">
            <project-additional/>
        </template>
        <template v-else-if="tab=='variables'">
            <project-variables/>
        </template>
        <template v-else>
            <project-form/>
        </template>
    </b-card>
</template>


<script>
    import ProjectForm from './ProjectForm';
    import ProjectVariables from './ProjectVariables';
    import ProjectAdditional from './ProjectAdditional';

    export default {
        name: "ProjectSettings",
        components:{
            ProjectForm,
            ProjectVariables,
            ProjectAdditional,
        },
        mounted(){
        },
        data: () => {
            return {
                tab:'settings'
            }
        },
        methods: {
        }
    }
</script>

<style scoped>

</style>