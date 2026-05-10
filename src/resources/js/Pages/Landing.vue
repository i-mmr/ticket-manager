<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Ticket Manager" />

  <main class="landing-page">
    <header class="site-header">
      <Link href="/" class="brand">Ticket Manager</Link>

      <nav class="nav-actions" aria-label="アカウントメニュー">
        <Link href="/login" class="login-link">Login</Link>
        <Link href="/register" class="signup-link">Sign up</Link>
      </nav>
    </header>

    <section class="hero-section">
      <div class="hero-copy">
        <p class="eyebrow">Support desk for focused teams</p>
        <h1>問い合わせを見逃さず、対応状況をすぐ共有。</h1>
        <p class="lead">
          Ticket Manager は、チケットの優先度、担当状況、対応履歴をチームで整理するためのシンプルな管理ツールです。
        </p>

        <div class="hero-actions">
          <Link href="/register" class="primary-action">無料で始める</Link>
          <Link href="/login" class="secondary-action">ログイン</Link>
        </div>
      </div>

      <div class="product-preview" aria-label="チケット管理画面のプレビュー">
        <div class="preview-toolbar">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <div class="preview-header">
          <div>
            <p>Today</p>
            <strong>24 tickets</strong>
          </div>
          <div class="preview-badge">Live</div>
        </div>

        <div class="ticket-row high">
          <span>高</span>
          <div>
            <strong>ログインエラーの調査</strong>
            <p>担当: Support A</p>
          </div>
        </div>
        <div class="ticket-row medium">
          <span>中</span>
          <div>
            <strong>請求書の再発行依頼</strong>
            <p>担当: Billing</p>
          </div>
        </div>
        <div class="ticket-row low">
          <span>低</span>
          <div>
            <strong>表示文言の修正</strong>
            <p>担当: Product</p>
          </div>
        </div>
      </div>
    </section>

    <section class="feature-band">
      <article>
        <span>01</span>
        <h2>優先度を可視化</h2>
        <p>緊急度の高いチケットから対応順を決められます。</p>
      </article>
      <article>
        <span>02</span>
        <h2>状況を共有</h2>
        <p>未対応、対応中、完了の状態を一覧で確認できます。</p>
      </article>
      <article>
        <span>03</span>
        <h2>認証つきで開始</h2>
        <p>会員登録後はメール確認を通してアカウントを有効化します。</p>
      </article>
    </section>
  </main>
</template>

<style scoped lang="scss">
@use '../../scss/abstracts/variables' as v;
@use '../../scss/abstracts/mixins' as m;

.landing-page {
  min-height: 100vh;
  background: #f8fbff;
  color: v.$color-text-strong;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;

  .site-header {
    width: min(1120px, calc(100% - 32px));
    min-height: 72px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .brand {
    color: v.$color-text-strong;
    font-size: 18px;
    font-weight: 900;
    text-decoration: none;
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .login-link,
  .signup-link,
  .primary-action,
  .secondary-action {
    @include m.button-base(42px, 16px);

    font-size: 14px;
  }

  .login-link,
  .secondary-action {
    border: 1px solid v.$color-border-soft;
    background: v.$color-surface;
    color: v.$color-text-strong;
  }

  .signup-link,
  .primary-action {
    background: v.$color-primary;
    color: #fff;
  }

  .hero-section {
    width: min(1120px, calc(100% - 32px));
    min-height: calc(100vh - 152px);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(360px, 500px);
    align-items: center;
    gap: 56px;
    padding: 28px 0 52px;
  }

  .eyebrow {
    @include m.eyebrow;

    margin-bottom: 14px;
    font-weight: 900;
  }

  h1 {
    max-width: 680px;
    margin: 0;
    font-size: clamp(40px, 7vw, 72px);
    line-height: 1.05;
  }

  .lead {
    max-width: 620px;
    margin: 22px 0 0;
    color: #475569;
    font-size: 18px;
    line-height: 1.8;
  }

  .hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 30px;
  }

  .product-preview {
    padding: 18px;
    border: 1px solid #334155;
    border-radius: v.$radius-md;
    background: v.$color-text-strong;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
  }

  .preview-toolbar {
    display: flex;
    gap: 7px;
    margin-bottom: 22px;

    span {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #94a3b8;
    }
  }

  .preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    color: #fff;

    p {
      margin: 0 0 4px;
      color: #94a3b8;
      font-size: 13px;
    }

    strong {
      font-size: 28px;
    }
  }

  .preview-badge {
    padding: 8px 10px;
    border-radius: v.$radius-md;
    background: #dcfce7;
    color: #166534;
    font-size: 12px;
    font-weight: 900;
  }

  .ticket-row {
    display: grid;
    grid-template-columns: 44px 1fr;
    align-items: center;
    gap: 14px;
    margin-top: 12px;
    padding: 14px;
    border-radius: v.$radius-md;
    background: #1e293b;
    color: #fff;

    span {
      display: grid;
      place-items: center;
      width: 36px;
      height: 36px;
      border-radius: v.$radius-md;
      font-size: 13px;
      font-weight: 900;
    }

    strong {
      display: block;
      margin-bottom: 4px;
    }

    p {
      margin: 0;
      color: v.$color-border-soft;
      font-size: 13px;
    }

    &.high span {
      background: #fee2e2;
      color: #991b1b;
    }

    &.medium span {
      background: #fef3c7;
      color: #92400e;
    }

    &.low span {
      background: #dbeafe;
      color: #1e40af;
    }
  }

  .feature-band {
    width: min(1120px, calc(100% - 32px));
    margin: 0 auto;
    padding: 0 0 56px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;

    article {
      padding-top: 18px;
      border-top: 1px solid v.$color-border-soft;
    }

    span {
      color: v.$color-primary;
      font-size: 13px;
      font-weight: 900;
    }

    h2 {
      margin: 10px 0 8px;
      font-size: 20px;
    }

    p {
      margin: 0;
      color: v.$color-muted;
      line-height: 1.7;
    }
  }
}

@media (max-width: 840px) {
  .landing-page {
    .hero-section {
      min-height: auto;
      grid-template-columns: 1fr;
      gap: 32px;
      padding-top: 20px;
    }

    .product-preview {
      order: -1;
    }

    .feature-band {
      grid-template-columns: 1fr;
    }
  }
}

@media (max-width: 520px) {
  .landing-page {
    .site-header {
      align-items: flex-start;
      flex-direction: column;
      padding: 16px 0;
    }

    .nav-actions {
      width: 100%;
    }

    .login-link,
    .signup-link {
      flex: 1;
    }

    h1 {
      font-size: 38px;
    }
  }
}
</style>
