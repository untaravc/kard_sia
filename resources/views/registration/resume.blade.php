<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="/css/app.css" rel="stylesheet" type="text/css"/>
    <title>Resume Registration</title>
    <style>
        .hover-pointer:hover {
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 2px 3px;
            border: 1px solid #ddd;
        }

        td {
            padding: 2px 3px;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f4f4f4;
        }

        tbody tr:hover {
            background-color: #cfcfcf;
        }

        .hidden {
            display: none;
        }

        .option-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            background: #ffffff;
        }

        .option-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .option-title h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .option-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .option-hint {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }

        .print-btn {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 12px;
            color: #111827;
        }

        .print-btn:hover {
            background: #f8fafc;
            cursor: pointer;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 6px 10px;
        }

        @media (max-width: 1100px) {
            .option-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 800px) {
            .option-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 520px) {
            .option-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            border-radius: 10px;
            border: 1px solid transparent;
            user-select: none;
        }

        .option-item:hover {
            background: #f8fafc;
            border-color: #e5e7eb;
        }

        .option-item input[type="checkbox"] {
            width: 14px;
            height: 14px;
        }

        .option-item label {
            font-size: 12px;
            color: #111827;
            line-height: 1.2;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0 !important;
            }

            table {
                font-size: 10px;
            }
        }
    </style>
</head>

<body class="p-2">
<h1 class="text-center">Resume Registration</h1>

<div id="app">
    <div class="option-card no-print" v-if="!is_printing">
        <div class="option-title">
            <h6>Options</h6>
            <div class="option-actions">
                <button type="button" class="print-btn" @click="printPage()">Print</button>
                <p class="option-hint">Toggle columns</p>
            </div>
        </div>
        <div class="option-grid">
            <div v-for="field in filed_list" :key="field.key" class="option-item hover-pointer">
                <input
                    type="checkbox"
                    class="hover-pointer"
                    :id="field.key"
                    v-model="field.checked"
                    @change="loadFields()"
                >
                <label class="hover-pointer" :for="field.key">@{{ field.label }}</label>
            </div>
        </div>
    </div>

    <div>
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th v-for="(head, h) in filed_list" :key="head.key" :class="isActiveField(head.key) ? '' : 'hidden'">@{{head.label}}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(data, d) in data_content.data">
                <td>@{{ d + 1 }}</td>
                <td v-for="(head, h) in filed_list" :key="head.key" :class="isActiveField(head.key) ? '' : 'hidden'">
                    <span v-if="head.type === 'text'">
                        @{{ formatValue(data[head.name]) }}
                    </span>
                    <span v-if="head.type === 'sub'">
                        @{{ formatValue((data[head.name] && data[head.name][head.sub] !== undefined && data[head.name][head.sub] !== null) ? data[head.name][head.sub] : '') }}
                    </span>
                    <span v-if="head.type === 'link'">
                        <a v-if="data[head.name]" :href="data[head.name]" target="_blank">
                        @{{ data[head.name] }}
                        </a>
                        <span v-else>-</span>
                    </span>
	                    <span v-if="head.type === 'details_list'">
	                        <ul v-if="Array.isArray(data[head.name]) && filterDetailsByLabel(data[head.name], head.labelFilter).length" style="margin:0; padding-left: 16px;">
	                            <li v-for="(item, i) in filterDetailsByLabel(data[head.name], head.labelFilter)" :key="(item && item.id) ? item.id : i">
	                                @{{ formatValue(item && (item.desc || item.name || item.place || item.year || '')) }};
	                            </li>
	                        </ul>
	                        <span v-else>-</span>
	                    </span>
                    <span v-if="head.type === 'detail_value'">
                        @{{ formatValue(findDetailValue(data[head.name], head.labelFilter, head.nameFilter)) || '-' }}
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const { createApp, ref, reactive, onMounted, onBeforeUnmount, nextTick } = Vue

    createApp({
        setup() {
            const filed_list = ref(
                [
                    {key: '1', name: 'name', label: 'Name', checked: true, type: 'text'},
                    {key: '4', name: 'test_order', label: 'Tes Ke-', checked: true, type: 'text'},
                    {key: '5', name: 'selection_path', label: 'Seleksi', checked: true, type: 'text'},
                    {key: '6', name: 'working_status', label: 'Status Bekerja', checked: true, type: 'text'},
                    {key: '7', name: 'video_url', label: 'Video Url', checked: false, type: 'link'},
                    {key: '9', name: 'score', sub: 'selection_path_multiplier', label: '[+] Nilai', checked: true, type: 'sub'},
                    {key: '10', name: 'score', sub: 'pns_multiplier', label: '[+] PNS', checked: true, type: 'sub'},
                    {key: '11', name: 'score', sub: 'origin_university_type', label: 'Univ Type', checked: false, type: 'sub'},
                    {key: '12', name: 'score', sub: 'origin_university_multiplier', label: 'Univ Poin', checked: true, type: 'sub'},
                    {key: '13', name: 'ip_commulative', label: 'IPK', checked: true, type: 'text'},
                    {key: '14', name: 'score', sub: 'quality_ipk', label: 'Bobot IPK', checked: true, type: 'sub'},
                    {key: '15', name: 'score', sub: 'quality_toefl', label: 'TOEFL', checked: true, type: 'sub'},
                    {key: '19', name: 'score', sub: 'quality_english', label: 'Konversi B.ing', checked: true, type: 'sub'},
                    {key: '20', name: 'score', sub: 'quality_tpa', label: 'TPA', checked: true, type: 'sub'},
                    {key: '21', name: 'score', sub: 'score_written_exam', label: 'Utul', checked: true, type: 'sub'},
                    {key: '22', name: 'score', sub: 'score_ecg', label: 'EKG', checked: true, type: 'sub'},
                    {key: '23', name: 'score', sub: 'score_written_exam_total', label: 'Utul Total', checked: true, type: 'sub'},
                    {key: '24', name: 'score', sub: 'quality_written_exam', label: 'Bobot Utul', checked: true, type: 'sub'},
                    {key: '25', name: 'score', sub: 'score_journal', label: 'Jurnal', checked: true, type: 'sub'},
                    {key: '26', name: 'score', sub: 'quality_journal', label: 'Bobot Jurnal', checked: true, type: 'sub'},
                    {key: '27', name: 'score', sub: 'subtotal_score', label: 'Nilai total', checked: true, type: 'sub'},
                    {key: '28', name: 'graduate_place', label: 'Tujuan Kerja Setelah Lulus ', checked: false, type: 'text'},
                    {key: '29', name: 'graduate_reason', label: 'Alasan Tujuan', checked: false, type: 'text'},
                    {key: '30', name: 'registration_details', label: 'Penghargaan', checked: false, type: 'details_list', labelFilter: 'acievement'},
                    {key: '31', name: 'registration_details', label: 'UKDI CBT', checked: false, type: 'detail_value', labelFilter: 'score', nameFilter: 'UKDI CBT'},
                ])

            const data_content = reactive({
                data: []
            })

            const is_printing = ref(false)

            function loadFields() {
                filed_list.value.filter(field => field.checked).map(field => field.name)
            }

            function loadDataContent() {
                const token = localStorage.getItem('token')
                if (!token) {
                    alert('Missing localStorage token (key: token)')
                    return;
                }

                const searchParams = new URLSearchParams(window.location.search || '')
                const registration_period = searchParams.get('registration_period') || ''

                const params = {
                    per_page: 45,
                    section: 'journal',
                }
                if (registration_period) {
                    params.registration_period = registration_period
                }

                axios.get('/api/registrations', {
                    params,
                    headers: {
                        Authorization: "Bearer " + token
                    }
                })
                    .then(({data}) => {
                        const rows = data && data.result && Array.isArray(data.result.data) ? data.result.data : []
                        rows.forEach((row) => {
                            if (!row.score) {
                                row.score = {}
                            }
                            if (!Array.isArray(row.registration_details)) {
                                row.registration_details = []
                            }
                        })
                        data_content.data = rows
                    })
            }

            loadDataContent()

            function isActiveField(key) {
                const checked_fields = filed_list.value.find(field => {
                    return field.checked && field.key === key
                })

                return !!checked_fields;
            }

            function formatValue(value) {
                if (value === null || value === undefined) return '';
                if (typeof value === 'number' && isFinite(value)) return value.toFixed(2);
                return value;
            }

            function filterDetailsByLabel(details, labelFilter) {
                if (!Array.isArray(details) || !details.length) return []
                const needle = (labelFilter || '').toString().trim().toLowerCase()
                if (!needle) return details

                return details.filter((d) => {
                    const label = (d && d.label) ? String(d.label).toLowerCase() : ''
                    return label === needle || label === 'achievement'
                })
            }

            function findDetailValue(details, labelFilter, nameFilter) {
                if (!Array.isArray(details) || !details.length) return ''
                const labelNeedle = (labelFilter || '').toString().trim().toLowerCase()
                const nameNeedle = (nameFilter || '').toString().trim().toLowerCase()

                const match = details.find((d) => {
                    const label = (d && d.label) ? String(d.label).toLowerCase() : ''
                    const name = (d && d.name) ? String(d.name).toLowerCase() : ''
                    const labelOk = !labelNeedle || label === labelNeedle
                    const nameOk = !nameNeedle || name === nameNeedle
                    return labelOk && nameOk
                })

                if (!match) return ''
                return match.desc ?? match.desc_1 ?? match.desc_2 ?? match.year ?? match.place ?? match.date ?? match.name ?? ''
            }

            function printPage() {
                is_printing.value = true
                nextTick(() => {
                    window.print()
                    is_printing.value = false
                })
            }

            function onKeydown(e) {
                const key = (e && e.key) ? String(e.key).toLowerCase() : ''
                const isPrintShortcut = (e && (e.ctrlKey || e.metaKey)) && key === 'p'
                if (!isPrintShortcut) return

                e.preventDefault()
                is_printing.value = true
                nextTick(() => {
                    window.print()
                    is_printing.value = false
                })
            }

            onMounted(() => {
                window.addEventListener('keydown', onKeydown)
            })

            onBeforeUnmount(() => {
                window.removeEventListener('keydown', onKeydown)
            })

            return {
                filed_list,
                loadDataContent,
                loadFields,
                isActiveField,
                formatValue,
                filterDetailsByLabel,
                findDetailValue,
                printPage,
                is_printing,
                data_content
            }
        }
    }).mount('#app')
</script>
</body>

</html>
