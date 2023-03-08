<template>
    <div class="content-wrapper px-3">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data Soal</label>
                                        <br>
                                        <input id="form_file" type="file">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>.</label> <br>
                                    <button class="btn btn-success" :disabled="disabled" @click="uploadData">
                                        <div class="spinner-border text-secondary" style="height: 20px; width: 20px"
                                             v-show="disabled"></div>
                                        <i class="fa fa-upload"></i> Upload
                                    </button>
                                </div>
                            </div>
                            <div class="row p-2">
                                <div class="col-12 border-warning" v-if="errors && errors.length > 0">
                                    <ul>
                                        <li v-for="err in errors">
                                            {{ err }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-12 border-success" v-if="message !== ''">
                                    {{ message }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            disabled: false,
            message: '',
            errors: ['Pilih File Excel untuk di upload.'],
            validation_data: [],
        }
    },
    methods: {
        uploadData() {
            const form_file = document.getElementById('form_file');

            const formData = new FormData();
            if (document.getElementById('form_file').value != '') {
                formData.append('file', form_file.files[0]);
            } else {
                alert('Lengkapi file dan tanggal!');
                return
            }

            this.disabled = true;
            this.errors = [];
            this.message = '';
            axios.post('/sadmin/import-soal', formData)
                .then(({data}) => {
                    if (data.status) {
                        this.message = data.message;
                    } else {
                        this.errors = data.errors;
                    }
                    document.getElementById('form_file').value = ''
                    this.disabled = false;
                }).catch((e) => {
                this.disabled = false;
            });
        },
    },
}
</script>
<style scoped>
.border-warning {
    border-radius: 5px;
    border: 2px solid hsl(39deg 82% 83%);
    background-color: hsl(53deg 100% 88%);
}

.border-success {
    border-radius: 5px;
    border: 2px solid hsl(94deg 42% 79%);
    background-color: hsl(95deg 100% 93%);
}

ul {
    padding-inline-start: 24px;
    margin-top: .7rem;
    margin-bottom: .7rem;
}
</style>
