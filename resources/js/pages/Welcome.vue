<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    canLogin: Boolean;
    canRegister: Boolean;
    laravelVersion: String;
    phpVersion: String;
    landingPage: String;
}>();

const page = usePage()
// const user = computed(() => page.props.auth.user)

</script>

<template>
    <Head title="Αρχική" />
 
    <!-- <div class="relative flex items-top justify-center min-h-screen bg-cover"
        style="background-image: url('images/background.png')">
        <header class="mb-6 w-full">
            <div class="fixed top-0 right-0 pr-8 pt-4 sm:block">
                <Link v-if="page.props.auth.user" :href="route('dashboard')"
                    class="text-indigo-800 underline font-semibold">
                Αρχική
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-indigo-800 underline font-semibold">
                    Είσοδος
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="text-indigo-800 underline font-semibold">
                    Εγγραφή
                    </Link>
                </template>
            </div>
        </header> -->
    <div
    class="relative flex items-top justify-center min-h-screen bg-cover"
    style="background-image: url('images/background.png')"
  >
    <div v-if="canLogin" class="fixed top-0 right-0 pr-8 pt-4 sm:block">
      <Link
        v-if="page.props.auth.user"
        :href="route(landingPage)"
        class="text-indigo-800 underline font-semibold"
      >
        Αρχική
      </Link>

      <template v-else>
        <Link
          :href="route('login')"
          class="text-indigo-800 underline font-semibold"
        >
          Είσοδος
        </Link>

        <Link
          v-if="canRegister"
          :href="route('register')"
          class="ml-2 text-indigo-800 underline font-semibold"
        >
          Εγγραφή
        </Link>
      </template>
    </div>


    <div class="max-w-6xl m-auto px-6">
      <div
        class="
          mt-4
          mx-2
          bg-white
          dark:bg-gray-800
          overflow-hidden
          shadow
          rounded-xl
        "
      >
        <div class="grid grid-cols-1">
          <a :href="route('about')">
            <img src="images/logo.png" />
          </a>
        </div>
      </div>
      <div class="mt-6 flex justify-center font-medium text-xl text-indigo-900">
        Ηλ.Απουσιολόγος&nbsp;v.3
      </div>
      <div class="mt-4 flex justify-center font-medium text-xl text-indigo-900">
        {{ page.props.schoolName }}
      </div>

      <div class="mt-2 text-center text-gray-900">
        GΘ@Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
      </div>
    </div>
  </div>




<!-- 
        <div
            class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">

            </main>
        </div>
        <div class="h-14.5 hidden lg:block"></div>
    </div> -->
</template>
