import fs from "fs/promises";
import path from "path";
import { pathToFileURL } from "url";

async function collectModuleAssetsPaths(paths, modulesPath) {
    modulesPath = path.join(modulesPath, "app-modules");

    try {
        // Read module directories
        const moduleDirectories = await fs.readdir(modulesPath);

        for (const moduleDir of moduleDirectories) {
            if (moduleDir === ".DS_Store" || moduleDir === ".gitignore") {
                // Skip .DS_Store directory
                continue;
            }
            const jsPath = path.join(modulesPath, moduleDir, "resources/js/module.js");
            // Check if the file exists
            try {
                await fs.access(jsPath);
                const finalJsPath = jsPath.split("app-modules")[1];
                paths.push(path.join("app-modules", finalJsPath));
            } catch (err) {}

            const cssPath = path.join(modulesPath, moduleDir, "resources/css/module.css");
            try {
                await fs.access(cssPath);
                const finalCssPath = cssPath.split("app-modules")[1];
                paths.push(path.join("app-modules", finalCssPath));
            } catch (err) {}
        }
    } catch (error) {
        console.error(`Error reading module statuses or module configurations: ${error}`);
    }
    console.log(paths);

    return paths;
}

export default collectModuleAssetsPaths;
