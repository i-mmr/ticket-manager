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

<style scoped>
.landing-page {
  min-height: 100vh;
  background: #f8fbff;
  color: #0f172a;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.site-header {
  width: min(1120px, calc(100% - 32px));
  margin: 0 auto;
  min-height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.brand {
  color: #0f172a;
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
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  box-sizing: border-box;
  border-radius: 8px;
  padding: 0 16px;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

.login-link,
.secondary-action {
  border: 1px solid #cbd5e1;
  color: #0f172a;
  background: #fff;
}

.signup-link,
.primary-action {
  background: #2563eb;
  color: #fff;
}

.hero-section {
  width: min(1120px, calc(100% - 32px));
  margin: 0 auto;
  min-height: calc(100vh - 152px);
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(360px, 500px);
  align-items: center;
  gap: 56px;
  padding: 28px 0 52px;
}

.eyebrow {
  margin: 0 0 14px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  max-width: 680px;
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
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 18px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}

.preview-toolbar {
  display: flex;
  gap: 7px;
  margin-bottom: 22px;
}

.preview-toolbar span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #94a3b8;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  color: #fff;
}

.preview-header p {
  margin: 0 0 4px;
  color: #94a3b8;
  font-size: 13px;
}

.preview-header strong {
  font-size: 28px;
}

.preview-badge {
  border-radius: 8px;
  padding: 8px 10px;
  background: #dcfce7;
  color: #166534;
  font-size: 12px;
  font-weight: 900;
}

.ticket-row {
  display: grid;
  grid-template-columns: 44px 1fr;
  gap: 14px;
  align-items: center;
  margin-top: 12px;
  padding: 14px;
  border-radius: 8px;
  background: #1e293b;
  color: #fff;
}

.ticket-row span {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 900;
}

.ticket-row strong {
  display: block;
  margin-bottom: 4px;
}

.ticket-row p {
  margin: 0;
  color: #cbd5e1;
  font-size: 13px;
}

.ticket-row.high span {
  background: #fee2e2;
  color: #991b1b;
}

.ticket-row.medium span {
  background: #fef3c7;
  color: #92400e;
}

.ticket-row.low span {
  background: #dbeafe;
  color: #1e40af;
}

.feature-band {
  width: min(1120px, calc(100% - 32px));
  margin: 0 auto;
  padding: 0 0 56px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.feature-band article {
  border-top: 1px solid #cbd5e1;
  padding-top: 18px;
}

.feature-band span {
  color: #2563eb;
  font-size: 13px;
  font-weight: 900;
}

.feature-band h2 {
  margin: 10px 0 8px;
  font-size: 20px;
}

.feature-band p {
  margin: 0;
  color: #64748b;
  line-height: 1.7;
}

@media (max-width: 840px) {
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

@media (max-width: 520px) {
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
</style>
