<template>
    <div>
        <h4>Варианты ответа <i class="nav-icon icon-plus" @click="addMenuItems()"></i></h4>

        <span class="text-danger" v-if="validateState('PresentReplies') === false " v-for="nErr in errors['PresentReplies']" >
                                            <span  v-text="nErr"></span><br>
                                        </span>
        <b-row v-for="(variant,key) in presentReplies" :key="'presentReplies'+key">
                <b-col sm="3">
                    <b-form-group>
                        <label>Пункт меню</label>
                        <b-form-select
                                id="present_reply_pointInputId"
                                :state="validateState('PresentReplies.'+key+'.point')"
                                :aria-describedby="'textLiveFeedback' + key+'.point'"
                                placeholder="Пункт" v-model="variant.point"
                                class="mb-3">
                            <option :value="null" selected>Выберите пункт меню</option>
                            <option v-for="point in points" :value="point" v-text="point"></option>
                        </b-form-select>
                        <b-form-invalid-feedback :id="'pointLiveFeedback' + key+'.point'" >
                            <template v-if="validateState('PresentReplies.'+key+'.point') === false " v-for="nErr in errors['PresentReplies.'+key+'.point']" >
                                <span  v-text="nErr" :key="'error_point'+key"></span><br>
                            </template>
                        </b-form-invalid-feedback>
                    </b-form-group>
                </b-col>
                <b-col sm="4">
                    <b-form-group>
                        <label>Текст</label>
                        <b-form-input type="text" id="present_reply_textInputId" placeholder="Текст" v-model="variant.text"
                                      :state="validateState('PresentReplies.'+key+'.text')"
                                      :aria-describedby="'textLiveFeedback' + key+'.text'"></b-form-input>

                        <b-form-invalid-feedback :id="'textLiveFeedback' +'.text'" >
                            <template v-if="validateState('PresentReplies.'+key+'.text') === false " v-for="nErr in errors['PresentReplies.'+key+'.text']" >
                                <span  v-text="nErr" :key="'error_text'+key"></span><br>
                            </template>
                        </b-form-invalid-feedback>
                    </b-form-group>
                </b-col>
                <b-col sm="1" class="remove-col">
                    <span>
                        <i class="nav-icon icon-close" @click="removeItem(key)"></i>
                    </span>
                </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        name: "PresentReplyForm",

        props:{
            presentReplies: {
                type: Array,
                required: true,
            },
            errors :{
                type: Object,
                required: false,
            },
            points :{
               type: Array,
               default:[]
            }
        },
        updated(){
            // console.log("Update",this.errors);
        },
        methods:{
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) ? false : null;
            },
            addMenuItems(){
                this.presentReplies.push({
                    point: null,
                    text: ""
                })
            },
            removeItem(index){
                this.presentReplies.splice(index,1);
            }

        }
    }
</script>

<style scoped>
    .remove-col, .default-col{
        padding-top: 34px;
    }
</style>