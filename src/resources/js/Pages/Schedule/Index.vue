<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '../../Components/AppLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
</script>

<template>
  <Head title="スケジュール" />

  <AppLayout active="schedule">
    <section class="placeholder-panel">
      <PageHeader title="スケジュール" description="スケジュール情報を確認できます。" />
    </section>
  </AppLayout>
</template>
