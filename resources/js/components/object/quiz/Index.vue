<template>
    <div class="row">
        <div class="col-12 pb-5">
            <div class="card mt-2" style="border-top: 7px solid #014385;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 v-if="data_content.section">{{ data_content.section.name }}</h3>
                            <span v-if="auth">Login sebagai : <b>{{ auth.email }}</b></span>
                        </div>
                        <div class="d-flex">
                            <span v-if="!saving" class="text-black-50" style="align-self: flex-end">
                                <i class="fa fa-save"></i>
                                <i>Saved</i>
                            </span>
                            <span v-if="saving" class="text-black-50" style="align-self: flex-end">
                                <i class="fa fa-save"></i>
                                <i>Saving...</i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-body">
                    <div class="form-group">
                        <h5 class="mb-4">Nama Lengkap</h5>
                        <input type="text" class="form-control" :disabled="auth" :value="auth.name">
                    </div>
                </div>
            </div>
            <div class="card mt-2" v-for="(quiz_section, i) in quiz_sections">
                <div class="card-body">
                    <div class="mb-3 d-flex" style="font-size: 1.2em">
                        {{ i + 1 }}. <span v-if="quiz_section.quiz" v-html="quiz_section.quiz.question"></span>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-radio" v-for="option in quiz_section.quiz.option_arr">
                            <input class="custom-control-input" type="radio"
                                   v-model="quiz_section.answered"
                                   :value="option.option"
                                   @change="selectOption(quiz_section, option)"
                                   :id="quiz_section.quiz.id + '_' + option.option"
                                   :name="'quiz'+quiz_section.quiz.id">
                            <label :for="quiz_section.quiz.id + '_' + option.option"
                                   class="custom-control-label">{{ option.text }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <button class="btn btn-sm btn-block btn-success"
                        :disabled="saving" @click="finishQuiz()">
                    <span v-if="saving" class="spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span>
                    Selesai
                </button>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            saving: false,
            data_content: {},
            auth: '',
            quiz_sections: [],
        }
    },
    methods: {
        loadQuiz() {
            let token = this.$route.query.t
            axios.get('/pub/quiz?t=' + token)
                .then(({data}) => {
                    if(data.status){
                        this.data_content = data.result;
                        this.auth = data.auth;
                        this.quiz_sections = data.quiz_sections;
                    }else{
                        alert(data.text);
                        window.location = '/'
                    }
                })
        },
        selectOption(quiz_section, option) {
            this.saving = true;
            axios.post('/pub/select_option', {
                quiz_student: this.data_content,
                quiz_section: quiz_section,
                option: option,
            }).then(({data}) => {
                setTimeout(()=>{
                    this.saving = false;
                }, 1000)

            }).catch(() => {
                this.saving = false;
            })
        },
        finishQuiz(){
            if(confirm('Anda telah menyelesaikan quiz?')){
                this.saving = true
                let token = this.$route.query.t
                axios.post('/pub/quiz-finish?t=' + token)
                    .then(({data})=>{

                        window.location = data.push
                        this.saving = false;
                    }).catch(()=>{
                        this.saving = false;
                })
            }
        }
    },
    created() {
        this.loadQuiz();
    }
}
</script>
