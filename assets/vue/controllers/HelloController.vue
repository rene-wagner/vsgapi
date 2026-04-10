<template>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hallo {{ name }}!</h5>
            <p class="card-text">Zähler: {{ count }}</p>
            <button class="btn btn-primary" @click="increment">
                <i class="fa-solid fa-plus"></i> Inkrementieren
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HelloController',
    props: {
        name: {
            type: String,
            default: 'Welt'
        },
        initialCount: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            count: this.initialCount
        };
    },
    methods: {
        increment() {
            this.count++;
        }
    }
};
</script>