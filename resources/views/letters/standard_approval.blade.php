<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Letter Approval</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f3f4f6;
            font-family: "Times New Roman", Times, serif;
            color: #111827;
        }
        .page {
            max-width: 794px;
            margin: 24px auto;
            background: #fff;
            padding: 28px 40px;
            border: 1px solid #e5e7eb;
        }
        .page + .page {
            margin-top: 24px;
        }
        .notice {
            border: 1px solid #fde68a;
            background: #fffbeb;
            padding: 10px 12px;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .actions {
            margin: 20px 0 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .btn {
            border: 1px solid #e5e7eb;
            background: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 12px;
            cursor: pointer;
            width: 100%;
        }
        .btn-primary {
            border-color: #0f766e;
            background: #0f766e;
            color: #fff;
        }
        .btn-danger {
            border-color: #e11d48;
            background: #fff;
            color: #e11d48;
        }
        .btn[disabled] { opacity: .55; cursor: not-allowed; }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .4);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 50;
        }
        .modal {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
        }
        .modal-title { font-weight: 700; font-size: 14px; margin: 0 0 6px; }
        .modal-text { font-size: 13px; margin: 0; color: #0f172a; }
        .modal-footer { margin-top: 14px; display: flex; justify-content: flex-end; gap: 10px; }
        .toast {
            margin-top: 10px;
            font-size: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: none;
        }
        .toast.ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .toast.err { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 14px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header .logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            flex: 0 0 auto;
        }
        .header .header-text {
            text-align: left;
        }
        .header .top {
            font-weight: 700;
            letter-spacing: .04em;
            line-height: 1.3;
            text-transform: uppercase;
            font-size: 13px;
        }
        .header .dept {
            margin-top: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            line-height: 1.35;
            text-transform: uppercase;
            font-size: 13px;
        }
        .header .addr {
            margin-top: 10px;
            font-size: 11px;
            line-height: 1.45;
        }
        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            font-size: 12px;
            margin-bottom: 14px;
        }
        .meta .right { text-align: right; }
        .meta-row { display: flex; gap: 10px; }
        .meta-row .key { width: 78px; flex: 0 0 auto; }
        .meta-row .val { flex: 1 1 auto; }
        .content {
            font-size: 13px;
            line-height: 1.7;
        }
        .content p { margin: 0; }
        .content table { margin-left: 40px; }
        .content table td,
        .content table th { vertical-align: top; }
        .attachment-content table {
            width: 100%;
            margin-left: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .attachment-content table td,
        .attachment-content table th { border: 1px solid #e5e7eb; }
        .content .ql-indent-1 { padding-left: 3em; }
        .content .ql-indent-2 { padding-left: 6em; }
        .content .ql-indent-3 { padding-left: 9em; }
        .content .ql-indent-4 { padding-left: 12em; }
        .content .ql-indent-5 { padding-left: 15em; }
        .content .ql-indent-6 { padding-left: 18em; }
        .content .ql-indent-7 { padding-left: 21em; }
        .content .ql-indent-8 { padding-left: 24em; }
        .content-section + .content-section { margin-top: 10px; }
        .signature-wrap {
            margin-top: 32px;
            display: flex;
            justify-content: flex-end;
        }
        .signature {
            width: 46%;
            min-width: 240px;
            text-align: center;
            font-size: 12px;
        }
        .signature .sig-label {
            font-weight: 700;
        }
        .signature .sig-space {
            height: 70px;
        }
        .signature .sig-name {
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .signature .sig-number {
            margin-top: 2px;
        }
        @media print {
            body { background: #fff; }
            .page { border: none; margin: 0; }
            .page { page-break-after: always; }
            .page:last-child { page-break-after: auto; }
            .notice { display: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="notice">
            Jangan bagikan link ini. Halaman ini khusus untuk proses persetujuan.
        </div>

        <div class="header">
            <img
                class="logo"
                alt="UGM Logo"
                src="https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FUGM-LOGO.png?alt=media&token=8e5ca470-6abb-42e5-b48a-5c2e7895b03e"
            />
            <div class="header-text">
                <div class="top" style="margin-bottom: 0">
                    {{ setting('app.university-name', 'UNIVERSITAS') }}<br />
                    {{ setting('app.faculty-name', 'FAKULTAS') }}
                </div>
                <div class="dept" style="margin-top: 0">
                    {{ setting('app.department-name', 'DEPARTEMEN') }}
                </div>
                <div class="addr" style="margin-top: 0">
                    Gedung Radioputro Lt 2 Sayap Barat, Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan
                    <br>Jl. Farmako Sekip Utara, Sleman. Telp.{{ setting('app.contact-phone', '0274-588688 ext 17230') }}, Fax.0274-631011, Email : {{ setting('app.contact-email', 'contact-email') }}
                </div>
            </div>
        </div>

        @php
            $hasAttachment = !empty(data_get($letter, 'attachment'))
                || !empty(data_get($letter, 'attachement'))
                || !empty(data_get($letter, 'attachment_url'))
                || !empty(data_get($letter, 'attachment_urls'))
                || !empty(data_get($letter, 'file'))
                || !empty(data_get($letter, 'link'));
            $lampiran = $hasAttachment ? 'satu bendel' : '-';
        @endphp

        <div class="meta">
            <div class="left">
                <div class="meta-row">
                    <div class="key">Nomor</div>
                    <div class="val">: {{ $letter->number }}</div>
                </div>
                <div class="meta-row">
                    <div class="key">Lampiran</div>
                    <div class="val">: {{ $lampiran }}</div>
                </div>
                <div class="meta-row">
                    <div class="key">Perihal</div>
                    <div class="val">: {{ $letter->title ?? '' }}
                        @if(!empty($letter->subtitle))
                            <div style="margin-left: 6px;">{{ $letter->subtitle }}</div>
                        @endif
                    </div>
                </div>
                <div style="margin-top: 10px; font-size: 12px;">
                    <div style="margin-bottom: 6px;"><strong>Kepada Yth.</strong></div>
                    @php
                        $inviteItems = $invites && $invites->count() ? $invites : collect();
                        $customInvitationLines = collect(preg_split("/\r\n|\r|\n/", (string) ($letter->custom_invitation ?? '')))
                            ->map(fn ($line) => trim($line))
                            ->filter(fn ($line) => $line !== '')
                            ->values();
                        $hasInvitationList = $inviteItems->count() || $customInvitationLines->count();
                    @endphp

                    @if($hasInvitationList)
                        <ol style="margin: 0; padding-left: 18px;">
                            @foreach($inviteItems as $invite)
                                <li style="margin: 2px 0;">
                                    {{ $invite->auth_name ?: ($invite->auth_type . ' #' . $invite->auth_id) }}
                                </li>
                            @endforeach
                            @foreach($customInvitationLines as $line)
                                <li style="margin: 2px 0;">{{ $line }}</li>
                            @endforeach
                        </ol>
                    @else
                        <div>-</div>
                    @endif
                </div>
            </div>
            <div class="right">
                <div><strong>Tanggal</strong>: {{ $letter->date ? date_indo_str($letter->date) : '-' }}</div>
            </div>
        </div>

        <div class="content">
            @if(!empty($letter->intro))
                <div class="content-section">
                    {!! $letter->intro !!}
                </div>
            @endif

            @if(!empty($letter->body))
                <div class="content-section">
                    {!! $letter->body !!}
                </div>
            @endif

            @if(!empty($letter->outro))
                <div class="content-section">
                    {!! $letter->outro !!}
                </div>
            @endif
        </div>

        @php
            $sigLabel = data_get($approval, 'label') ?: 'Kepala Departemen';
            $sigName = data_get($approval, 'auth_name') ?: (data_get($approval, 'auth_type') . ' #' . data_get($approval, 'auth_id'));
            $sigNumber = data_get($approval, 'auth_number') ?: data_get($approval, 'auth_id');
        @endphp

        <div class="signature-wrap">
            <div class="signature">
                <div class="sig-label">{{ $sigLabel }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $sigName }}</div>
                <div class="sig-number">{{ $sigNumber }}</div>
            </div>
        </div>

        <div class="actions" data-letter-token="{{ data_get($letter, 'token') }}" data-participant-token="{{ data_get($approval, 'token') }}">
            <button class="btn btn-primary" type="button" data-action="approve">Setujui</button>
            <button class="btn btn-danger" type="button" data-action="reject">Batalkan</button>
        </div>
        <div id="approval-toast" class="toast"></div>
    </div>

    @if(!empty($letter->attachement_content))
        <div class="page">
            <div class="header">
                <img
                    class="logo"
                    alt="UGM Logo"
                    src="https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FUGM-LOGO.png?alt=media&token=8e5ca470-6abb-42e5-b48a-5c2e7895b03e"
                />
                <div class="header-text">
                    <div class="top" style="margin-bottom: 0">
                        {{ setting('app.university-name', 'UNIVERSITAS') }}<br />
                        {{ setting('app.faculty-name', 'FAKULTAS') }}
                    </div>
                    <div class="dept" style="margin-top: 0">
                        {{ setting('app.department-name', 'DEPARTEMEN') }}
                    </div>
                    <div class="addr" style="margin-top: 0">
                        Gedung Radioputro Lt 2 Sayap Barat, Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan
                        <br>Jl. Farmako Sekip Utara, Sleman. Telp.{{ setting('app.contact-phone', '0274-588688 ext 17230') }}, Fax.0274-631011, Email : {{ setting('app.contact-email', 'contact-email') }}
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <div style="font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 13px;">
                    Lampiran
                </div>
                @if(!empty($letter->attachment_label))
                    <div style="margin-top: 6px; font-size: 12px;">
                        {{ $letter->attachment_label }}
                    </div>
                @endif
            </div>

            <div class="content attachment-content">
                {!! $letter->attachement_content !!}
            </div>
        </div>
    @endif

    <div id="approval-modal" class="modal-backdrop" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true">
            <p id="approval-modal-title" class="modal-title">Konfirmasi</p>
            <p id="approval-modal-text" class="modal-text"></p>
            <div class="modal-footer">
                <button id="approval-modal-cancel" class="btn" type="button">Cancel</button>
                <button id="approval-modal-confirm" class="btn btn-primary" type="button">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const actionWrap = document.querySelector('.actions[data-letter-token][data-participant-token]');
            if (!actionWrap) return;

            const letterToken = actionWrap.getAttribute('data-letter-token');
            const participantToken = actionWrap.getAttribute('data-participant-token');
            const approveBtn = actionWrap.querySelector('[data-action="approve"]');
            const rejectBtn = actionWrap.querySelector('[data-action="reject"]');

            const modal = document.getElementById('approval-modal');
            const modalText = document.getElementById('approval-modal-text');
            const modalCancel = document.getElementById('approval-modal-cancel');
            const modalConfirm = document.getElementById('approval-modal-confirm');
            const toast = document.getElementById('approval-toast');

            let pendingStatus = null;
            let submitting = false;

            const endpoint = `/api/letters/${encodeURIComponent(letterToken)}/process-approval/${encodeURIComponent(participantToken)}`;

            const showModal = (status) => {
                pendingStatus = status;
                modalText.textContent = status === 1
                    ? 'Anda yakin ingin menyetujui surat ini?'
                    : 'Anda yakin ingin membatalkan persetujuan surat ini?';
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            };

            const hideModal = () => {
                if (submitting) return;
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                pendingStatus = null;
            };

            const showToast = (kind, message) => {
                toast.className = `toast ${kind}`;
                toast.textContent = message;
                toast.style.display = 'block';
            };

            const setDisabled = (disabled) => {
                approveBtn.disabled = disabled;
                rejectBtn.disabled = disabled;
                modalCancel.disabled = disabled;
                modalConfirm.disabled = disabled;
            };

            const submit = () => {
                if (submitting || !pendingStatus) return;
                submitting = true;
                setDisabled(true);

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status: pendingStatus }),
                })
                    .then(async (res) => {
                        const data = await res.json().catch(() => null);
                        if (!res.ok) {
                            const msg = data && data.text ? data.text : 'Gagal memproses persetujuan.';
                            throw new Error(msg);
                        }
                        showToast('ok', 'Berhasil diproses.');
                        hideModal();
                    })
                    .catch((err) => {
                        showToast('err', err && err.message ? err.message : 'Terjadi kesalahan.');
                    })
                    .finally(() => {
                        submitting = false;
                        setDisabled(false);
                    });
            };

            approveBtn.addEventListener('click', () => showModal(1));
            rejectBtn.addEventListener('click', () => showModal(2));
            modalCancel.addEventListener('click', hideModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });
            modalConfirm.addEventListener('click', submit);
        })();
    </script>
</body>
</html>
