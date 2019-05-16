<template>
  <div class="app flex-row align-items-center">
    <div class="container">
      <b-row class="justify-content-center">
        <b-col md="5">
          <b-card-group>
            <b-card no-body class="p-4">
              <b-card-body>
                <b-form>
                  <h1>Вход</h1>
                  <p class="text-muted">Войти в личный кабинет</p>
                  <b-alert v-show="errors.signin" show variant="danger">{{ errors.signin }}</b-alert>
                  <b-input-group class="mb-3">
                    <b-input-group-prepend><b-input-group-text><i class="icon-user"></i></b-input-group-text></b-input-group-prepend>
                    <b-form-input
                            v-model="username"
                            type="text"
                            :state="validateState('username')"
                            aria-describedby="login_id"
                            class="form-control"
                            placeholder="Логин"
                            autocomplete="username email"
                            />
                    <b-form-invalid-feedback id="login_id" >
                      <template v-if="validateState('username') === false" v-for="nErr in errors['username']">
                        <span v-text="nErr"></span><br>
                      </template>
                    </b-form-invalid-feedback>
                  </b-input-group>
                  <b-input-group class="mb-4">
                    <b-input-group-prepend><b-input-group-text><i class="icon-lock"></i></b-input-group-text></b-input-group-prepend>
                    <b-form-input v-model="password" type="password" :state="validateState('password')" class="form-control" aria-describedby="password_id" placeholder="Пароль" autocomplete="current-password"/>
                    <b-form-invalid-feedback id="password_id" >
                      <template v-if="validateState('password') === false" v-for="nErr in errors['password']">
                        <span v-text="nErr"></span><br>
                      </template>
                    </b-form-invalid-feedback>
                  </b-input-group>
                  <b-row>
                    <b-col cols="6">
                      <b-button variant="primary" class="px-4" @click="singin()">Войти</b-button>
                    </b-col>
                    <!--<b-col cols="6" class="text-right">
                      <b-button variant="link" class="px-0">Forgot password?</b-button>
                    </b-col>-->
                  </b-row>
                </b-form>
              </b-card-body>
            </b-card>
            <!--<b-card no-body class="text-white bg-primary py-5 d-md-down-none" style="width:44%">
              <b-card-body class="text-center">
                <div>
                  <h2>Sign up</h2>
                  <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                  <b-button variant="primary" class="active mt-3">Register Now!</b-button>
                </div>
              </b-card-body>
            </b-card>-->
          </b-card-group>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
    export default {
        name: 'Login',
        data(){
            return {
                username: "",
                password: "",
                errors: [],
                hasError: false
            }
        },
        methods: {
            validateState (field) {
                return (this.errors[field] !== undefined && this.errors[field].length) || this.errors.signin ? false : null;
            },
            singin(){
                this.error= "";
                axios.post(configs.apiUrl + "/login",{ username:this.username, password: this.password }).then(
                    response => {
                        const { data } = response;
                        localStorage.setItem('token',data.token);
                        localStorage.setItem('user',JSON.stringify(data.user));
                        this.$user.set(Object.assign(data.user, { role: data.user.role.name }));
                        this.$router.push('/dashboard');
                    }).catch(error=>{
                    this.errors = error.response.data.errors;
                    this.hasError = true;
                });
                /*if(this.username && this.password) {

                }
                else{
                    this.hasError = true;
                }*/
            }
        }
    }
</script>
