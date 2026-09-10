<style>
    body {
        font-size: 12px;
        font-family: 'Times New Roman', Times, serif;
        color: #111827;
        margin: 0;
    }

    .sheet {
        width: 100%;
        max-width: 780px;
        margin: 0 auto;
        padding: 24px 28px;
        box-sizing: border-box;
    }

    /* Body shares the letterhead's left edge. */
    .content {
        margin: 0;
    }

    table tr td {
        vertical-align: top;
        font-size: 12px;
        padding: 0;
    }

    p {
        margin: 0 0 8px;
        line-height: 1.55;
        text-align: justify;
    }

    .letter-date {
        text-align: right;
        margin-bottom: 10px;
    }

    .letter-title {
        text-align: center;
        margin-bottom: 14px;
    }

    .letter-title h4 {
        text-decoration: underline;
        margin: 4px 0;
        font-size: 14px;
    }

    /* Key / separator / value rows. Fixed label and colon columns keep every
       value on one left edge, which is what makes the letter read as aligned. */
    .kv {
        width: 100%;
        border-collapse: collapse;
    }

    .kv td {
        padding: 1px 0;
        line-height: 1.45;
    }

    .kv td.k {
        width: 130px;
    }

    .kv td.s {
        width: 14px;
    }

    .kv.narrow td.k {
        width: 95px;
    }

    .indent {
        margin: 6px 0 10px 32px;
    }

    .recipients {
        margin: 0 0 10px;
    }

    .recipients ol {
        margin: 2px 0 0;
        padding-left: 22px;
    }

    .recipients li {
        line-height: 1.45;
    }

    .sign-block {
        width: 100%;
        margin-top: 26px;
        border-collapse: collapse;
    }

    .sign-spacer {
        width: 55%;
    }

    .sign-cell {
        width: 45%;
        line-height: 1.5;
    }

    .sign-qr {
        padding: 8px 0 6px;
    }

    .sign-qr img {
        width: 105px;
        height: 105px;
    }

    .verify-bar {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        border-radius: 8px;
        padding: 10px 14px;
        margin: 0 0 16px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.5;
    }

    @media print {
        .verify-bar {
            display: none;
        }

        .sheet {
            padding: 0;
            max-width: none;
        }
    }

    @page {
        size: A4;
        margin: 14mm;
    }
</style>
