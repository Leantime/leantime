import testmu, { resolveVar, setVar } from '@testmuai/playwright-bindings';
import { expect } from '@playwright/test';

testmu.configure({
  "build": "fc43571c-fe79-4917-8d28-4dcb42fd9922",
  "name": "Create New Project",
  "tcId": "TC-179",
  "network": (process.env.NETWORK ?? "false").toLowerCase() === "true",
  "autoHealVersion": "AH2",
  "defaultActionTimeoutMs": 10000,
  "defaultNavigationTimeoutMs": 60000,
  "kaneRunV4": true
});

testmu.test("Create New Project", async (page) => {
  let element_0, element_1, element_3, element_2, element_5;
  await testmu.step("Navigate to http://localhost:5080/auth/login", { instructionId: "2175810d-b96a-4df9-bda1-e273e9405980" }, async () => {
    await page.goto("http://localhost:5080/auth/login");
  });
  
  await testmu.step("Waiting for the next queued step to run", { instructionId: "ab6a5341-26fd-4366-bdf5-ac2d735406b7" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Type \"test@leantime.io\" into \"Email\"", { instructionId: "dc66af7c-1383-4410-9488-aeb7d70cc82e" }, async () => {
    element_0 = page.locator("internal:role=textbox[name=\"Email\"i]");
    await element_0.click();
    await element_0.fill("test@leantime.io");
  });
  
  await testmu.step("Type \"Test123456!\" into \"Password\"", { instructionId: "781d2814-e78f-4d67-a6f7-52284d82cbb2" }, async () => {
    element_1 = page.locator("internal:role=textbox[name=\"Password\"i]");
    await element_1.click();
    await element_1.fill("Test123456!");
  });
  
  await testmu.step("Pressing the Enter key to submit the login form", { instructionId: "8f570ecf-539d-4b7e-8501-64dea1ea55ee" }, async () => {
    await page.keyboard.press("Enter");
  });
  
  await testmu.step("Waiting 1500 ms for the dashboard page to finish loading", { instructionId: "a3307e83-646d-4429-8639-c14e7fde03f4" }, async () => {
    await page.waitForTimeout(1500);
  });
  
  await testmu.step("Reading the current page URL", { instructionId: "40e2ae83-f3f0-4c4d-86ab-0972a21665d7" }, async () => {
    setVar("dashboard_url_check", page.url());
  });
  
  await testmu.step("Assertion check", { instructionId: "5206e046-b325-4474-b9e6-02157a4840f1" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "the URL contains \"/dashboard\"", "passed": true, "operator": "contains", "transforms": [], "json_path": null, "expected": "/dashboard", "extracted_value": "{{dashboard_url_check}}", "store_key": "dashboard_url_check", "variable_refs": {"{{dashboard_url_check}}": "http://localhost:5080/dashboard/home"}}], "sub_checks": [{"description": "the URL contains \"/dashboard\"", "store_key": "dashboard_url_check", "expected_value": "/dashboard", "extracted_value": "{{dashboard_url_check}}", "operator": "contains", "transforms": []}], "composite_operator": "and", "claim": "the URL contains \"/dashboard\""});
  });
  
  await testmu.step("Navigate to http://localhost:5080/projects/newProject", { instructionId: "b462301c-c913-4896-a210-e264166ca8d0" }, async () => {
    await page.goto("http://localhost:5080/projects/newProject");
  });
  
  await testmu.step("Waiting for New Project page to finish loading", { instructionId: "c5a8f28a-c74e-44b2-9061-4f802a1c090f" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Checking whether the welcome dialog is shown", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("cp_1", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {155: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (!!(el(155).offsetWidth || el(155).offsetHeight || el(155).getClientRects().length));\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"Welcome to your Project!\"i]"], query: "whether the welcome dialog heading is visible", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "a welcome dialog is shown" }));
  });
  
  if (await testmu.evaluateBranch([{"description": "a welcome dialog is shown", "store_key": "cp_1", "expected_value": "true", "stored_value": "{{cp_1}}", "operator": "equals", "transforms": []}], "and")) {
    await testmu.step("Clicking Close to dismiss the welcome dialog", { instructionId: "86f5b5d6-cf3d-41a6-8d09-e0d20432d8bc" }, async () => {
      element_3 = page.locator("internal:role=link[name=\"\uf00d Close\"i]");
      await element_3.click();
    });
  } else {
    page = await testmu.executeKaneCli("do nothing", page);
  }
  
  await testmu.step("Waiting for the welcome dialog to complete", { instructionId: "35d712fe-ccd3-42a1-b2ce-d2d8c45c17a4" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Typing the project title in the project details form", { instructionId: "c817231e-8182-4a4b-ba5e-4328a1b5df27" }, async () => {
    element_2 = page.locator("internal:role=textbox[name=\"Enter the title of this\"i]");
    await element_2.click();
    await element_2.fill("Website Relaunch");
  });
  
  await testmu.step("Checking whether the New Project heading is present", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("new_project_visible", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {62: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(62).textContent.trim() === 'New Project');\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"New Project\"i]"], query: "whether the New Project heading is present", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "the page shows \"New Project\"" }));
  });
  
  await testmu.step("Reading the project title field value", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("title_field_website_relaunch", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {69: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(69).value);\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=textbox[name=\"Enter the title of this\"i]"], query: "the value in the project title field", expectedValue: "Website Relaunch", needsUnitConversion: false, operator: "equals", transforms: ["strip"], condition: "the title field contains \"Website Relaunch\"" }));
  });
  
  await testmu.step("Assertion check", { instructionId: "07ca001e-5d6e-4203-b66c-f6befce17b2c" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "the page shows \"New Project\"", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{new_project_visible}}", "store_key": "new_project_visible", "variable_refs": {"{{new_project_visible}}": "true"}}, {"description": "the title field contains \"Website Relaunch\"", "passed": true, "operator": "equals", "transforms": ["strip"], "json_path": null, "expected": "Website Relaunch", "extracted_value": "{{title_field_website_relaunch}}", "store_key": "title_field_website_relaunch", "variable_refs": {"{{title_field_website_relaunch}}": "Website Relaunch"}}], "sub_checks": [{"description": "the page shows \"New Project\"", "store_key": "new_project_visible", "expected_value": "true", "extracted_value": "{{new_project_visible}}", "operator": "equals", "transforms": []}, {"description": "the title field contains \"Website Relaunch\"", "store_key": "title_field_website_relaunch", "expected_value": "Website Relaunch", "extracted_value": "{{title_field_website_relaunch}}", "operator": "equals", "transforms": ["strip"]}], "composite_operator": "and", "claim": "the page shows \"New Project\" and the title field contains \"Website Relaunch\""});
  });
  
  await testmu.step("Clicking Save project button", { instructionId: "4b17b057-78d0-459f-b23b-7b317c399775" }, async () => {
    element_5 = page.locator("internal:role=button[name=\"Save\"i]");
    await element_5.click();
  });
  
  await testmu.step("Waiting for the project details page to finish loading", { instructionId: "3d84312a-255d-4d91-a0ff-3406eebafdf5" }, async () => {
    await page.waitForTimeout(1500);
  });
  
  await testmu.step("whether a welcome dialog is visible on the page", { instructionId: "6f112600-6d7a-4741-ab4e-b648b6f32588" }, async () => {
    setVar("welcome_dialog_shown", await testmu.visionQuery(page, "whether a welcome dialog is visible on the page", ""));
  });
  
  if (await testmu.evaluateBranch([{"description": "a welcome dialog is shown", "store_key": "welcome_dialog_shown", "expected_value": "true", "stored_value": "{{welcome_dialog_shown}}", "operator": "equals", "transforms": []}], "and")) {
    page = await testmu.executeKaneCli("click its \\\"Close\\\" button", page);
  }
  
  await testmu.step("Reading the current project name from the header", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("current_project_header_check", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {62: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(62).textContent.trim().replace(/^Project:\\s*/, ''));\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=heading[name=\"Project: Website Relaunch\"i]"], query: "the current project name shown in the page header", expectedValue: "Website Relaunch", needsUnitConversion: false, operator: "equals", transforms: ["strip"], condition: "the current project shown in the header is \"Website Relaunch\"" }));
  });
  
  await testmu.step("Assertion check", { instructionId: "b4067d3c-a203-4c95-8aea-ec3e30e45eb4" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "the current project shown in the header is \"Website Relaunch\"", "passed": true, "operator": "equals", "transforms": ["strip"], "json_path": null, "expected": "Website Relaunch", "extracted_value": "{{current_project_header_check}}", "store_key": "current_project_header_check", "variable_refs": {"{{current_project_header_check}}": "Website Relaunch"}}], "sub_checks": [{"description": "the current project shown in the header is \"Website Relaunch\"", "store_key": "current_project_header_check", "expected_value": "Website Relaunch", "extracted_value": "{{current_project_header_check}}", "operator": "equals", "transforms": ["strip"]}], "composite_operator": "and", "claim": "the current project shown in the header is \"Website Relaunch\""});
  });
});

testmu.run();