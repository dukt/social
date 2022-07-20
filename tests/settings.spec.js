const {test, expect} = require('@playwright/test');

test('Shoud show the login providers Settings page', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/settings/social/loginproviders');
  const title = page.locator('h1');
  await expect(title).toHaveText('Social Settings');
});