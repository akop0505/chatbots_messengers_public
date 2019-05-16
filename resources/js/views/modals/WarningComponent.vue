<template>
    <b-modal centered id="warning"
             ref="modalWarning"
             header-bg-variant="warning"
             header-text-variant="dark"
             title="Предупреждение"
             ok-title="OK"
             v-model="modal"
    >
        <b-container fluid>
            <span class="text-dark">{{ warningText }}</span>
        </b-container>
        <div slot="modal-footer" class="w-100">
            <b-btn class="float-right" variant="primary" @click="toggle()">
                OK
            </b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        name: "WarningComponent",
        methods: {
            toggle(text){
                this.modal = !this.modal;
                if(this.modal && text !== undefined){
                    this.warningText = text;
                }else{
                    this.warningText = "";
                }
            }
        },
        data:()=>{
            return {
                warningText: "",
                modal: false
            }
        }
    }
</script>

<style scoped>

</style>