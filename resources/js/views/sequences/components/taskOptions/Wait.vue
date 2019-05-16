<template>
    <input-group>
        <input-group-text>
            От
        </input-group-text>
        <b-form-input type="text" v-model="value.from"></b-form-input>
        <input-group-text>
            до
        </input-group-text>
        <b-form-input type="text" v-model="value.to"></b-form-input>

        <input-group-text class="no-style">
            <model-list-select
                    :class="{'form-control':true}"
                    :list="types"
                    v-model="value.type"
                    option-value="type"
                    option-text="name">
            </model-list-select>
        </input-group-text>

    </input-group>
</template>

<script>

    import { sequencesTaskWaitTypes } from '../../../../configs/sequences';
    import InputGroup from "bootstrap-vue/es/components/input-group/input-group";
    import InputGroupPrepend from "bootstrap-vue/es/components/input-group/input-group-prepend";
    import InputGroupText from "bootstrap-vue/es/components/input-group/input-group-text";
    import { ModelListSelect } from 'vue-search-select';

    export default {
        components: {
            ModelListSelect,
            InputGroupText,
            InputGroupPrepend,
            InputGroup},
        name: "wait",
        props: {
            'value': {
                type: Object,
                required: true,
            },
        },
        data: function () {
            return {
                types: sequencesTaskWaitTypes
            };
        },
    }
</script>

<style scoped>
    .no-style{
        padding: 0px;
        border: 0px;
    }
    .no-style /deep/ .dropdown.form-control {
        min-height: 10px;
        line-height: 10px;
    }
</style>