<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Letter Preview</title>
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
        .meta .left, .meta .right { flex: 1; }
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
        .content .ql-indent-1 { padding-left: 3em; }
        .content .ql-indent-2 { padding-left: 6em; }
        .content .ql-indent-3 { padding-left: 9em; }
        .content .ql-indent-4 { padding-left: 12em; }
        .content .ql-indent-5 { padding-left: 15em; }
        .content .ql-indent-6 { padding-left: 18em; }
        .content .ql-indent-7 { padding-left: 21em; }
        .content .ql-indent-8 { padding-left: 24em; }
        .content-section + .content-section { margin-top: 10px; }
        .section {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .section h2 {
            margin: 0 0 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #374151;
        }
        .pill {
            display: inline-block;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 6px 10px;
            border-radius: 999px;
            margin: 4px 6px 0 0;
        }
        .signature-wrap {
            margin-top: 32px;
            display: flex;
            gap: 26px;
        }
        .signature-wrap.one {
            justify-content: flex-end;
        }
        .signature-wrap.two {
            justify-content: space-between;
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
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <img
                class="logo"
                alt="UGM Logo"
                src="https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FUGM-LOGO.png?alt=media&token=8e5ca470-6abb-42e5-b48a-5c2e7895b03e"
            />
            <div class="header-text">
                <div class="top" style="margin-bottom: 0">
                    UNIVERSITAS GADJAH MADA<br />
                    FAKULTAS KEDOKTERAN, KESEHATAN MASYARAKAT, DANKEPERAWATAN
                </div>
                <div class="dept" style="margin-top: 0">
                    DEPARTEMEN KARDIOLOGI DAN KEDOKTERAN VASKULAR
                </div>
                <div class="addr" style="margin-top: 0">
                    Gedung Radioputro Lt 2 Sayap Barat, Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan
                    <br>Jl. Farmako Sekip Utara, Sleman. Telp.0274-588688 ext 17230, Fax.0274-631011, Email : kardiologi@ugm.co.id
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
                    <div style="margin-bottom: 6px;">Kepada Yth.</div>
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
                <div>{{ $letter->date ? date_indo_str($letter->date) : '-' }}</div>
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
            $approvals = $letter->participants ? $letter->participants->where('type', 'approval')->values() : collect();
            if ($approvals->isEmpty() && !empty($approval)) {
                $approvals = collect([$approval]);
            }
            $approvals = $approvals->take(2)->values();
        @endphp

        @if($approvals->count())
            <div class="signature-wrap {{ $approvals->count() === 2 ? 'two' : 'one' }}">
                @foreach($approvals as $appr)
                    @php
                        $sigLabel = data_get($appr, 'label') ?: 'Kepala Departemen';
                        $sigName = data_get($appr, 'auth_name') ?: (data_get($appr, 'auth_type') . ' #' . data_get($appr, 'auth_id'));
                        $sigNumber = data_get($appr, 'auth_number') ?: data_get($appr, 'auth_id');
                    @endphp
                    <div class="signature">
                        <div class="sig-label">{{ $sigLabel }}</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $sigName }}</div>
                        <div class="sig-number">{{ $sigNumber }}</div>
                    </div>
                @endforeach
            </div>
        @endif
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
                        UNIVERSITAS GADJAH MADA<br />
                        FAKULTAS KEDOKTERAN, KESEHATAN MASYARAKAT, DANKEPERAWATAN
                    </div>
                    <div class="dept" style="margin-top: 0">
                        DEPARTEMEN KARDIOLOGI DAN KEDOKTERAN VASKULAR
                    </div>
                    <div class="addr" style="margin-top: 0">
                        Gedung Radioputro Lt 2 Sayap Barat, Fakultas Kedokteran Kesehatan Masyarakat dan Keperawatan
                        <br>Jl. Farmako Sekip Utara, Sleman. Telp.0274-588688 ext 17230, Fax.0274-631011, Email : kardiologi@ugm.co.id
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <div style="font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 13px;">
                    Lampiran
                </div>
            </div>

            <div class="content">
                {!! $letter->attachement_content !!}
            </div>
        </div>
    @endif
</body>
</html>
