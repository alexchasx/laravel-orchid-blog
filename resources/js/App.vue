<template>
    <button @click="addLike">like</button>
    <button @click="addDisLike">dislike</button>
    <div>Работает1! {{ likes }}</div>
    <div>Работает2! {{ dislikes }}</div>

    <form>
        <p class="comment-notes">
            <span id="email-notes"
                >Ваш адрес электронной почты не будет опубликован.</span
            >
            <span class="required-field-message" aria-hidden="true">
                Обязательные поля отмечены
                <span class="required star" aria-hidden="true">*</span>
            </span>
        </p>

        <p class="comment-form-comment textwrapper">
            <label for="comment">
                {{ __("Комментарий") }}
                <span class="required star" aria-hidden="true">*</span>
                <br />
                <span class="invalid-feedback">{{ $message }}</span>
            </label>
            <br />
            <textarea
                id="comment"
                name="comment"
                cols="35"
                rows="8"
                maxlength="65525"
                required="required"
                >{{ old("comment") }}</textarea
            >
        </p>
    </form>
</template>
<script>
export default {
    data() {
        return {
            likes: 0,
            dislikes: 0,
        };
    },
    methods: {
        addLike() {
            this.likes += 1;
        },
        addDisLike() {
            this.dislikes += 1;
        },
    },
};
</script>
<style></style>
