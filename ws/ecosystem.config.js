module.exports = {
    apps: [{
        name: 'server',
        script: './src/server.js',
        env: {
            NODE_ENV: 'development',
            NODE_TLS_REJECT_UNAUTHORIZED: 0,
        },
        env_production: {
            NODE_ENV: 'production',
            NODE_TLS_REJECT_UNAUTHORIZED: 0,
        },
    }],
}
