# Tailwind CSS build and cPanel deployment

The UI is being migrated to Tailwind CSS v4. Theme tokens are in `resources/css/app.css`. Use `gereh-*` tokens and utility classes in CI4 PHP views.

Build on a developer machine or CI runner:

```bash
npm install
npm run build:css
```

Upload the generated `public/assets/css/app.css` with the PHP application to cPanel. No Node.js runtime is required on the host.

GitHub Actions builds and uploads a CSS artifact for commits to main. The artifact is not automatically committed or deployed; download it and include it in the deployment. Existing inline CSS in legacy views remains until each view is migrated and visually verified. Do not delete legacy CSS before the generated bundle and the page have been checked.
