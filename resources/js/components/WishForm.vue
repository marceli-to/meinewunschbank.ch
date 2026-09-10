<script setup>
import { reactive, ref } from 'vue';
import FormCheckbox from './form/FormCheckbox.vue';
import FormInput from './form/FormInput.vue';
import FormLabel from './form/FormLabel.vue';
import FormTextarea from './form/FormTextarea.vue';
import PhotoUpload from './form/PhotoUpload.vue';
import { wishFormDefaults } from '../support/wishFormDefaults';

const props = defineProps({
    title: { type: String, default: 'Jetzt Herzenswunsch einreichen' },
    // Set by the server outside production — see the block template.
    prefill: { type: Boolean, default: false },
});

// One flat object — mirrors the payload SubmitWishRequest validates, so a
// server-side error key maps straight onto a field.
const form = reactive({
    photo: null,
    wish: '',
    link: '',
    firstname: '',
    lastname: '',
    street: '',
    city: '',
    email: '',
    phone: '',
    birthdate: '',
    accepts_terms: false,
    accepts_publication: false,
});

// Everything but the photo, which a browser will not let us set.
if (props.prefill) {
    Object.assign(form, wishFormDefaults());
}

const errors = ref({});
const sending = ref(false);
const done = ref(false);
const failed = ref(false);

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function submit() {
    if (sending.value) {
        return;
    }

    sending.value = true;
    failed.value = false;
    errors.value = {};

    const payload = new FormData();

    Object.entries(form).forEach(([key, value]) => {
        if (value === null || value === '') {
            return;
        }

        // Laravel's `accepted` rule wants a truthy scalar, not "true"/"false".
        payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
    });

    try {
        const response = await fetch('/api/wishes', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: payload,
        });

        if (response.status === 422) {
            const body = await response.json();
            errors.value = Object.fromEntries(
                Object.entries(body.errors ?? {}).map(([key, messages]) => [key, messages[0]]),
            );

            // Put the first offending field in view — the form is long enough
            // that an error above the fold would otherwise go unnoticed.
            document.getElementById(Object.keys(errors.value)[0])?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

            return;
        }

        if (! response.ok) {
            failed.value = true;

            return;
        }

        done.value = true;
    } catch {
        failed.value = true;
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <section>
        <h2 class="font-bold leading-[1.1] text-lg md:text-2xl lg:text-4xl mb-32 md:mb-48 lg:mb-64">
            {{ title }}
        </h2>

        <div v-if="done" role="status" class="border border-brand p-20 md:p-32">
            <p class="font-bold">Vielen Dank für Ihren Herzenswunsch!</p>
            <p class="mb-0">Wir haben Ihre Einreichung erhalten und melden uns bei Ihnen.</p>
        </div>

        <form v-else novalidate class="flex flex-col gap-40 md:gap-56 lg:gap-72" @submit.prevent="submit">
            <fieldset>
                <legend class="w-full font-bold text-md md:text-lg lg:text-xl mb-16 md:mb-20 border-b border-brand pb-8">
                    Ihr Foto auf der Wunschbank *
                </legend>
                <PhotoUpload v-model="form.photo" :error="errors.photo" />
            </fieldset>

            <fieldset>
                <legend class="w-full font-bold text-md md:text-lg lg:text-xl mb-16 md:mb-20">
                    Was ist Ihr Herzenswunsch? *
                </legend>
                <FormTextarea
                    id="wish"
                    v-model="form.wish"
                    :rows="5"
                    :error="errors.wish"
                    placeholder="Beschreiben Sie hier Ihren Wunsch möglichst konkret. Teilen Sie uns mit, ob der Wunsch für Sie selbst, für eine andere Person oder für einen Verein bzw. eine Organisation bestimmt ist. Erzählen Sie uns, weshalb Ihnen dieser Wunsch am Herzen liegt und weshalb gerade er erfüllt werden sollte." />
            </fieldset>

            <fieldset>
                <legend class="w-full font-bold text-md md:text-lg lg:text-xl mb-16 md:mb-20">
                    Möchten Sie Ihren Wunsch mit einem Link ergänzen?
                </legend>
                <FormTextarea
                    id="link"
                    v-model="form.link"
                    :rows="2"
                    :error="errors.link"
                    placeholder="Falls es zu Ihrem Wunsch weitere Informationen gibt, können Sie den entsprechenden Link hier angeben." />
            </fieldset>

            <fieldset>
                <legend class="w-full font-bold text-md md:text-lg lg:text-xl mb-8 md:mb-12">
                    Ihre Kontaktdaten *
                </legend>
                <p class="mb-24 md:mb-32">
                    Hinweis: Herzenswünsche können von Personen ab 18 Jahren eingereicht werden.
                    Wünsche für Kinder und Jugendliche sind selbstverständlich willkommen.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-24 lg:gap-x-32 gap-y-20 md:gap-y-24">
                    <div>
                        <FormLabel for="firstname">Vorname</FormLabel>
                        <FormInput id="firstname" v-model="form.firstname" :error="errors.firstname" autocomplete="given-name" />
                    </div>
                    <div>
                        <FormLabel for="lastname">Nachname</FormLabel>
                        <FormInput id="lastname" v-model="form.lastname" :error="errors.lastname" autocomplete="family-name" />
                    </div>
                    <div>
                        <FormLabel for="street">Strasse, Nr.</FormLabel>
                        <FormInput id="street" v-model="form.street" :error="errors.street" autocomplete="street-address" />
                    </div>
                    <div>
                        <FormLabel for="city">PLZ, Ort</FormLabel>
                        <FormInput id="city" v-model="form.city" :error="errors.city" autocomplete="postal-code" />
                    </div>
                    <div>
                        <FormLabel for="email">E-Mail-Adresse</FormLabel>
                        <FormInput id="email" v-model="form.email" type="email" :error="errors.email" autocomplete="email" />
                    </div>
                    <div>
                        <FormLabel for="phone">Telefonnummer</FormLabel>
                        <FormInput id="phone" v-model="form.phone" type="tel" :error="errors.phone" autocomplete="tel" />
                    </div>
                    <div>
                        <FormLabel for="birthdate">Geburtsdatum</FormLabel>
                        <FormInput id="birthdate" v-model="form.birthdate" type="date" :error="errors.birthdate" autocomplete="bday" />
                    </div>
                </div>
            </fieldset>

            <div class="flex flex-col gap-20 md:gap-24">
                <FormCheckbox id="accepts_terms" v-model="form.accepts_terms" :error="errors.accepts_terms">
                    Ich habe die Teilnahmebedingungen gelesen und akzeptiere sie.
                </FormCheckbox>
                <FormCheckbox id="accepts_publication" v-model="form.accepts_publication" :error="errors.accepts_publication">
                    Ich bin damit einverstanden, dass im Falle einer Wunscherfüllung mein Vor- und Nachname,
                    mein Foto sowie Informationen zu meinem Herzenswunsch veröffentlicht werden
                    (Website, Social Media).
                </FormCheckbox>
            </div>

            <div class="flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-16">
                <button
                    type="submit"
                    :disabled="sending"
                    class="btn inline-flex w-auto self-start font-bold leading-none px-20 py-12 lg:px-24 lg:py-16 rounded-full bg-brand text-white cursor-pointer hover:bg-brand/80 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ sending ? 'Wird gesendet …' : 'Absenden' }}
                </button>
                <p class="mb-0 text-xxs">* Pflichtfelder</p>
            </div>

            <p v-if="failed" role="alert" class="mb-0 text-error">
                Das hat leider nicht geklappt. Bitte versuchen Sie es später noch einmal.
            </p>
        </form>
    </section>
</template>
